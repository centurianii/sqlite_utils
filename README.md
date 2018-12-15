# sqlite_utils
Some SQLite database utilities in php.
--------------------------------------
We'll try to analyze a SQLite database file.

1. **Boolean return**: Let's check if a given file is SQLite:

```
<?php
$file = '/some/file.sq';

error_log('Checking boolean result of SQLite::analyze($file)');
if(SQLite::analyze($file))
   error_log("The file {$file} IS SQLite db!");
else
   error_log("The file {$file} is NOT SQLite db!");
```
2. **Array return**: Let's check in base 10 a array of specific properties from SQLite:
```
<?php
<?php
$file = '/some/file.sq';

$file = '/some/file.sq';
error_log('Checking in base 10 version, encoding and number of pages SQLite::analyze($file, VERSION | PAGES | ENCODING)');
$tmp = SQLite::analyze($file, VERSION | PAGES | ENCODING);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 10: ".print_r($tmp, true));
```
Sample output:
<pre>
(
    [BASE] => 10
    [PAGES] => 35
    [ENCODING] => 1
    [VERSION] => 3011000
)
</pre>

3. **Array return**: Let's check in base 16 previous array of properties from SQLite:
```
<?php
$file = '/some/file.sq';

error_log('Checking in base 16 version, encoding and number of pages SQLite::analyze($file, VERSION | PAGES | ENCODING)');
$tmp = SQLite::analyze($file, VERSION | PAGES | ENCODING, 16);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 16: ".print_r($tmp, true));
```
Sample output:
<pre>
(
    [BASE] => 16
    [PAGES] => 00000023
    [ENCODING] => 00000001
    [VERSION] => 002df1b8
)
</pre>

4. **String return**: Let's check in base 10 a string of specific properties from SQLite:
```
<?php
$file = '/some/file.sq';

error_log('Checking a stringify result in base 10 of version, encoding and number of pages SQLite::stranalyze($file, VERSION | PAGES | ENCODING)');
$tmp = SQLite::stranalyze($file, VERSION | PAGES | ENCODING);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 10: ".$tmp);
```
Sample output:
<pre>
BASE:10, PAGES:35, ENCODING:1, VERSION:3011000
</pre>

5. **String return**: Let's check in base 16 previous string of specific properties from SQLite:
```
<?php
$file = '/some/file.sq';

error_log('Checking a stringify result in base 16 of version, encoding and number of pages SQLite::stranalyze($file, VERSION | PAGES | ENCODING)');
$tmp = SQLite::stranalyze($file, VERSION | PAGES | ENCODING, 16);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 16: ".$tmp);
 ```
 Sample output:
 <pre>
 BASE:16, PAGES:00000023, ENCODING:00000001, VERSION:002df1b8
 </pre>
 
 6. **String return**: Let's check in base 10 a string of all properties from SQLite:
 ```
 <?php
$file = '/some/file.sq';

error_log('Checking a stringify result in base 10 of ALL detected fields SQLite::stranalyze($file, -1)');
$tmp = SQLite::stranalyze($file, -1);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 10: ".$tmp);
```
Sample output:
<pre>
BASE:10, PAGE_SIZE:1024, WRITE_FORMAT:1, READ_FORMAT:1, PAGE_RESERVED:0, MAX_PAYLOAD:64, MIN_PAYLOAD:32, LEAF_PAYLOAD:32, FILE_COUNTER:6982, PAGES:35, FREELIST_PAGE:0, FREELIST_PAGES:0, COOKIE:158, SCHEMA_FORMAT:4, PAGE_CACHE:0, MAX_PAGE:0, ENCODING:1, USER_VERSION:0, VACUUM:0, ID:0, RESERVED:0, VERSION_VALID:6982, VERSION:3011000
</pre>

 7. **String return**: Let's check in base 16 previous string of all properties from SQLite:
```
<?php
$file = '/some/file.sq';

error_log('Checking a stringify result in base 16 of ALL detected fields SQLite::stranalyze($file, -1, 16)');
$tmp = SQLite::stranalyze($file, -1, 16);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 16: ".$tmp); 
```
Sample output:
<pre>
BASE:16, PAGE_SIZE:0400, WRITE_FORMAT:01, READ_FORMAT:01, PAGE_RESERVED:00, MAX_PAYLOAD:40, MIN_PAYLOAD:20, LEAF_PAYLOAD:20, FILE_COUNTER:00001b46, PAGES:00000023, FREELIST_PAGE:00000000, FREELIST_PAGES:00000000, COOKIE:0000009e, SCHEMA_FORMAT:00000004, PAGE_CACHE:00000000, MAX_PAGE:00000000, ENCODING:00000001, USER_VERSION:00000000, VACUUM:00000000, ID:00000000, RESERVED:0000000000000000000000000000000000000000, VERSION_VALID:00001b46, VERSION:002df1b8
</pre>

That's all!
Have fun!
