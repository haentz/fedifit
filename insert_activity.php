<?php
/*
composer
    - "legomolina/simple-orm": "^2.0",
    - "sibyx/phpgpx": "1.2.1"

nginx user write permissions to temp fileupload (mac /opt/homebrew/var/run/nginx/client_body_temp/)
nginx write persimssion to final uplaod directory /uoploads/

*/


require_once('include/db.inc.php');

$target_dir = "uploads/";
$uploadOk = 0;

// IF UPLOADED
if(isset($_POST["submit"])) {


// test gpx file for xml and potential malicious code, file size etc
$uploadOk = 1;

//generate gpx file name
// create unique hash

$targetfilename = "a1.gpx";
$target_file = $target_dir . $targetfilename;

// check if gpx already uploaded




// simplify GPX?????



// save gpx file
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
  // if everything is ok, try to upload file
  } else {
     //TODO test if GPX or FIT


    //test to not overwrite
    if (move_uploaded_file($_FILES["gpxfile"]["tmp_name"], $target_file)) {
      echo "The file ". htmlspecialchars( basename( $_FILES["gpxfile"]["name"])). " has been uploaded.";

      ////// test if file xml (move out of this file)
      $data = file_get_contents($target_file);
      $result = simplexml_load_string ($data, 'SimpleXmlElement', LIBXML_NOERROR+LIBXML_ERR_FATAL+LIBXML_ERR_NONE);
      if (false == $result) echo 'error in xml';



    } else {
      echo "Sorry, there was an error uploading your file.";
    }
}
}

//extract key fields (speed, duration ,date) fom gpx file

// create Activity
// Create a new entity
$activity = $orm->create(Activity::class);

// Set user's name
$activity->setFkiduser(1);
$activity->setCreationdate(new DateTime());
$activity->setActivitydate(new DateTime());
// Persist our entity

//print_r($activity);

//insert Activity
if ($orm->save($activity)) {
    echo "Entity was saved";
} else {
    die("Something went wrong");
}


// render preview image for map (move this step into a rendering queue)




?>
<!DOCTYPE html>
<html>
<body>

<form action="insert_activity.php" method="post" enctype="multipart/form-data">
  Select image to upload:
  <input type="file" name="gpxfile" id="gpxfile">
  <input type="submit" value="Upload GPX" name="submit">
</form>

</body>
</html>