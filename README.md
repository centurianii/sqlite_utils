# sqlite_utils
Some SQLite database utilities in php.
======================================

Let's check if a given file is SQLite:

```
<?php
$file = '/some/file.sq';

error_log('Checking boolean result of SQLite::analyze()');
if(SQLite::analyze($file))
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} IS SQLite db!");

error_log('Checking boolean result of SQLite::analyze()');
if(SQLite::analyze($file))
   error_log("The file {$file} is NOT SQLite db!");
else
   error_log("The file {$file} IS SQLite db!");
```
