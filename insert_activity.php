<?php
/*
composer
   
    - "sibyx/phpgpx": "1.2.1"

nginx user write permissions to temp fileupload (mac /opt/homebrew/var/run/nginx/client_body_temp/)
nginx write persimssion to final uplaod directory /uoploads/

nano /opt/homebrew/etc/php/8.2/php.ini
*/


require_once('include/db.inc.php');
use phpGPX\phpGPX;


$target_dir = "uploads/";
$uploadOk = 0;

// IF UPLOADED
if(isset($_POST["submit"])) {



// create Activity
// Create a new entity
$activity = $orm->create(Activity::class);

// Set user's name
$activity->setFkiduser(1);
$activity->setCreationdate(new DateTime());
$activity->setHash( hash('ripemd160', "saltactivty"."1".$activity->getCreationdate()->format('Y-m-d-H-i-s')));
// Persist our entity

//print_r($activity);

//insert Activity
if ($orm->save($activity)) {
    echo "Entity was saved";
} else {
    die("Something went wrong");
}






// test gpx file for xml and potential malicious code, file size etc
$uploadOk = 1;

//generate gpx file name
// create unique hash


// check if gpx already uploaded


// simplify GPX?????
$targetfilename = hash('ripemd160', 'saltgpxfile'.'1'.$activity->getCreationdate()->format('Y-m-d-H-i-s')).".gpx";
$target_file = $target_dir . $targetfilename;



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
$gpx = new phpGPX();
	
$file = $gpx->load($target_file);
//print_r($file);
//read gpx
$distance = 0;
$duration = 0;
$ascend = 0;
$activitydate = null;
$trackNumber = 0;
foreach ($file->tracks as $track)
{
 //print_r($track);
  $trackNumber++;
  if($trackNumber==1) {
    $distance = $track->stats->distance;
    $duration =  $track->stats->duration;
    $ascend = $track->stats->cumulativeElevationGain;
    $activitydate = $track->stats->startedAt;
 


  }
  //TODO log number of trakcs with id of activity
  // Statistics for whole track
    
}  

$activity->setActivityFile($targetfilename);
//extract key fields (speed, duration ,date) fom gpx file
$activity->setActivitydate($activitydate);
$activity->setDistance((integer)round($distance));

 $activity->setDuration((integer)round($duration));
$activity->setAscend((integer)round($ascend));
$orm->save($activity);
//$activity->set();

// Persist our entity


// render preview image for map (move this step into a rendering queue)




?>