
<!DOCTYPE html>
<html>
<body>

<form action="insert_activity.php" method="post" enctype="multipart/form-data">
  Select image to upload:
  <input type="file" name="gpxfile" id="gpxfile">
  <input type="submit" value="Upload GPX" name="submit">
</form>

</body>
</html><?php

// require_once('include/db.inc.php');

// $activities = $orm(Activity::class)->all();
// print_r($activities);

phpinfo();
?>