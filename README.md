# sqlite_utils
Some SQLite database utilities in php.
--------------------------------------

Let's check if a given file is SQLite:

```
<?php
$file = '/some/file.sq';

error_log('Checking boolean result of SQLite::analyze($file)');
if(SQLite::analyze($file))
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} IS SQLite db!");

error_log('Checking in base 10 version, encoding and number of pages SQLite::analyze($file, VERSION & PAGES & ENCODING)');
$tmp = SQLite::analyze($file, VERSION & PAGES & ENCODING);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 10: ".print_r($tmp, true));

error_log('Checking in base 16 version, encoding and number of pages SQLite::analyze($file, VERSION & PAGES & ENCODING)');
$tmp = SQLite::analyze($file, VERSION & PAGES & ENCODING, 16);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 16: ".print_r($tmp, true));

error_log('Checking a stringify result in base 10 of version, encoding and number of pages SQLite::stranalyze($file, VERSION & PAGES & ENCODING)');
$tmp = SQLite::stranalyze($file, VERSION & PAGES & ENCODING);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 10: ".$tmp);

error_log('Checking a stringify result in base 16 of version, encoding and number of pages SQLite::stranalyze($file, VERSION & PAGES & ENCODING)');
$tmp = SQLite::stranalyze($file, VERSION & PAGES & ENCODING, 16);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 16: ".$tmp);
 
error_log('Checking a stringify result in base 10 of ALL detected fields SQLite::stranalyze($file, -1)');
$tmp = SQLite::stranalyze($file, -1);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 10: ".$tmp);

error_log('Checking a stringify result in base 16 of ALL detected fields SQLite::stranalyze($file, -1, 16)');
$tmp = SQLite::stranalyze($file, -1, 16);
if($tmp === false)
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} info in base 16: ".$tmp); 
```
