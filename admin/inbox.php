<?php 
require_once('../include/db.inc.php');
require_once($basedir.'/include/lib_helper.inc.php');

error_log('inbox request dump: '.(new DumpHTTPRequestToFile)->execute());

//https://rhiaro.co.uk/2016/05/minimal


// part of the actiovity pub  server API
//follow -> save to db
/**
  {
   "@context":"https://www.w3.org/ns/activitystreams",
   "id":"https://mastodon.social/4e30d791-ee54-4133-93b9-b84d2195ba4c",
   "type":"Follow",
   "actor":"https://mastodon.social/users/haentz",
   "object":"https://567c-95-89-45-59.ngrok-free.app/user/haentz"
}
 */
//reniove?
//like -> like++
?>