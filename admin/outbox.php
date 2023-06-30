<?php 
require_once('../include/db.inc.php');
require_once($basedir.'/include/lib_helper.inc.php');

error_log('outbox request dump: '.(new DumpHTTPRequestToFile)->execute());
// part of the actiovity pub  server API
//follow -> save to db
//reniove?
//like -> like++
?>