<?php

require_once('include/db.inc.php');

$activities = $orm(Activity::class)->all();
print_r($activities);

?>