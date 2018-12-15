<?php
// part of a bigger project ->
//namespace g3;

/**
 * SQLite management class.
 */
class SQLite {
   protected $dbh;
   protected $file;
   
   const PRE = 'g3' . NAMESPACE_DELIM;
   const ALL            = -1;
   const NAME           = 0;
   const PAGE_SIZE      = 2097152;//1000000000000000000000
   const WRITE_FORMAT   = 1048576;//0100000000000000000000
   const READ_FORMAT    = 524288;//0010000000000000000000
   const PAGE_RESERVED  = 262144;//0001000000000000000000
   const MAX_PAYLOAD    = 131072;//0000100000000000000000
   const MIN_PAYLOAD    = 65536;
   const LEAF_PAYLOAD   = 32768;
   const FILE_COUNTER   = 16384;
   const PAGES          = 8192;
   const FREELIST_PAGE  = 4096;
   const FREELIST_PAGES = 2048;
   const COOKIE         = 1024;
   const SCHEMA_FORMAT  = 512;
   const PAGE_CACHE     = 256;
   const MAX_PAGE       = 128;
   const ENCODING       = 64;
   const USER_VERSION   = 32;
   const VACUUM         = 16;
   const ID             = 8;
   const RESERVED       = 4;
   const VERSION_VALID  = 2;
   const VERSION        = 1;
   
   /**
    * Builds an instance of SQLite that encapsulates service properties and methods
    * like database handler, table creation and data insertion methods.
    * 
    * @param string|PDO The filepath of a database or an existed connection as a PDO object
    * 
    * @return SQLite An object of this class
    */
   public function __construct($file) {
      if (\is_a($file, 'PDO')) {
         $this->dbh = $file;
      } elseif (\is_string($file)) {
         $this->file = $file;
         $this->dbh = new \PDO('sqlite:'.$this->file);
      }
      // part of a bigger project ->
      //$tmp = Registry::getInstance()->getToken(self::PRE . 'ExceptionToken')->get("pdo_error_mode");
      //if ($tmp == 'ERRMODE_SILENT')
         //$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
      //elseif ($tmp == 'ERRMODE_WARNING')
         //$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
      //elseif ($tmp == 'ERRMODE_EXCEPTION')
         $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
   }
   
   /**
    * It checks the first 100 bytes of a SQLite database and the return depends 
    * on the flags used.
    * 
    * Available info in byte positions and flags used as 2nd argument:
    * 00 - 16:NAME               The header string: "SQLite format 3\000".
    * 16 - 02:PAGE_SIZE          The database page size in bytes. Must be a power of 
    *                            two between 512 and 32768 inclusive, or the value 1 
    *                            representing a page size of 65536.
    * 18 - 01:WRITE_FORMAT       File format write version. 1 for legacy; 2 for WAL.
    * 19 - 01:READ_FORMAT        File format read version. 1 for legacy; 2 for WAL.
    * 20 - 01:PAGE_RESERVED      Bytes of unused "reserved" space at the end of each page. Usually 0.
    * 21 - 01:MAX_PAYLOAD        Maximum embedded payload fraction. Must be 64.
    * 22 - 01:MIN_PAYLOAD        Minimum embedded payload fraction. Must be 32.
    * 23 - 01:LEAF_PAYLOAD       Leaf payload fraction. Must be 32.
    * 24 - 04:FILE_COUNTER       File change counter.
    * 28 - 04:PAGES              Size of the database file in pages. The "in-header database size".
    * 32 - 04:FREELIST_PAGE      Page number of the first freelist trunk page.
    * 36 - 04:FREELIST_PAGES     Total number of freelist pages.
    * 40 - 04:COOKIE             The schema cookie.
    * 44 - 04:SCHEMA_FORMAT      The schema format number. Supported schema formats are 1, 2, 3, and 4.
    * 48 - 04:PAGE_CACHE         Default page cache size.
    * 52 - 04:MAX_PAGE           The page number of the largest root b-tree page when in auto-vacuum 
    *                            or incremental-vacuum modes, or zero otherwise.
    * 56 - 04:ENCODING           The database text encoding. A value of 1 means UTF-8. A value of 
    *                            2 means UTF-16le. A value of 3 means UTF-16be.
    * 60 - 04:USER_VERSION       The "user version" as read and set by the user_version pragma.
    * 64 - 04:VACUUM             True (non-zero) for incremental-vacuum mode. False (zero) otherwise.
    * 68 - 04:ID                 The "Application ID" set by PRAGMA application_id.
    * 72 - 20:RESERVED           Reserved for expansion. Must be zero.
    * 92 - 04:VERSION_VALID      The version-valid-for number.
    * 96 - 04:VERSION            SQLITE_VERSION_NUMBER.
    * -------:BASE               The arithmetic base used. Hexadecimal or decimal.
    * 
    * @param string $f The filepath to check
    * @param int $flag A boolean `&` of constants; use SQLite::ALL to get all 
    *    fields; it defaults to `NAME` or `0` which returns `true/false`
    * @param int $convert Converts binary data read to base 10 or 16; defaults to 10
    * @return mixed[] If can't open the file or not SQLite it returns `false`; 
    *    if SQlite and `$flag == 0` it returns true; for other flags it returns 
    *    a hash array of constants converted to strings and used as keys
    **/
   public static function analyze($f, $flag = 0, $convert = 10){
      $result = array();
      $fh = @\fopen($f, "rb");
      
      //shared lock and read file
      if(\flock($fh, LOCK_SH | LOCK_NB)){
         $contents[] = \fread($fh, 16);
         
         // 1. NO SQLite db
         if(stristr($contents[0], 'SQLite') === FALSE) {
            fclose($fh); 
            return false;
         }    
         
         // 2. SQLite db
         if((int)$flag == 0){
            fclose($fh); 
            return true;
         }
         
         // 2.1. Convert -1 flag to a binary string
         if((int)$flag == -1)
            $flag = 0b1111111111111111111111;
         //else
            //$flag = \decbin($flag).'';
         
         // 2.2. Binary data converter to hex/dec base
         if($convert == 10){
            $f = function($v){return \hexdec(\bin2hex($v));};
            $result['BASE'] = 10;
         }else{
            $f = '\bin2hex';
            $result['BASE'] = 16;
         }
         
         // 2.3. Read binary data
         $tmp = \call_user_func_array($f, array(\fread($fh, 2)));
         if($flag & self::PAGE_SIZE) $result['PAGE_SIZE'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag & self::WRITE_FORMAT) $result['WRITE_FORMAT'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag & self::READ_FORMAT) $result['READ_FORMAT'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag & self::PAGE_RESERVED) $result['PAGE_RESERVED'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag & self::MAX_PAYLOAD) $result['MAX_PAYLOAD'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag & self::MIN_PAYLOAD) $result['MIN_PAYLOAD'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag & self::LEAF_PAYLOAD) $result['LEAF_PAYLOAD'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::FILE_COUNTER) $result['FILE_COUNTER'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::PAGES) $result['PAGES'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::FREELIST_PAGE) $result['FREELIST_PAGE'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::FREELIST_PAGES) $result['FREELIST_PAGES'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::COOKIE) $result['COOKIE'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::SCHEMA_FORMAT) $result['SCHEMA_FORMAT'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::PAGE_CACHE) $result['PAGE_CACHE'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::MAX_PAGE) $result['MAX_PAGE'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::ENCODING) $result['ENCODING'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::USER_VERSION) $result['USER_VERSION'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::VACUUM) $result['VACUUM'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::ID) $result['ID'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 20)));
         if($flag & self::RESERVED) $result['RESERVED'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::VERSION_VALID) $result['VERSION_VALID'] = $tmp;
         $tmp = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag & self::VERSION) $result['VERSION'] = $tmp;
         /*if($flag[0] == '1') $result['PAGE_SIZE'] = \call_user_func_array($f, array(\fread($fh, 2)));
         if($flag[1] == '1') $result['WRITE_FORMAT'] = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag[2] == '1') $result['READ_FORMAT'] = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag[3] == '1') $result['PAGE_RESERVED'] = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag[4] == '1') $result['MAX_PAYLOAD'] = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag[5] == '1') $result['MIN_PAYLOAD'] = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag[6] == '1') $result['LEAF_PAYLOAD'] = \call_user_func_array($f, array(\fread($fh, 1)));
         if($flag[7] == '1') $result['FILE_COUNTER'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[8] == '1') $result['PAGES'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[9] == '1') $result['FREELIST_PAGE'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[10] == '1') $result['FREELIST_PAGES'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[11] == '1') $result['COOKIE'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[12] == '1') $result['SCHEMA_FORMAT'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[13] == '1') $result['PAGE_CACHE'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[14] == '1') $result['MAX_PAGE'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[15] == '1') $result['ENCODING'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[16] == '1') $result['USER_VERSION'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[17] == '1') $result['VACUUM'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[18] == '1') $result['ID'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[19] == '1') $result['RESERVED'] = \call_user_func_array($f, array(\fread($fh, 20)));
         if($flag[20] == '1') $result['VERSION_VALID'] = \call_user_func_array($f, array(\fread($fh, 4)));
         if($flag[21] == '1') $result['VERSION'] = \call_user_func_array($f, array(\fread($fh, 4)));*/
         fclose($fh); 
         return $result;
      
      //shared lock and read file failed
      }else
         \fclose($fh);
      return false;
   }
   
   /**
    * Stringifies the results of {@link SQLite.analyze}.
    *
    * @param string $f The filepath to check
    * @param int $flag A boolean `&` of constants; use SQLite::ALL to get all 
    *    fields; it defaults to `NAME` or `0` which returns `true/false`
    * @param int $convert Converts binary data read to base 10 or 16; defaults to 10
    * @return mixed[] If can't open the file or not SQLite it returns `false`; 
    *    if SQlite and `$flag == 0` it returns true; for other flags it returns 
    *    a stringified hash array of constants converted to strings and used as keys
    */
   public static function stranalyze($f, $flag = 0, $convert = 10){
      if(\is_bool($result = self::analyze($f, $flag, $convert)))
         return $result;
      else{
         $arr = array();
         foreach($result as $key=>$value)
            $arr[] = $key.':'.$value;
         return \implode(', ', $arr);
      }
   }
}
