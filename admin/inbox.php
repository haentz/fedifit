<?php 
require_once('../include/db.inc.php');
require_once($basedir.'/include/lib_helper.inc.php');

$request = json_decode(file_get_contents('php://input'));
// filter spam calls
if($request->actor!='https://mastodon.social/users/haentz') die;

//   foreach ($_SERVER as $name => $value) {
// error_log($name .":".$value);

//   }
//error_log('inbox request dump: '.(new DumpHTTPRequestToFile)->execute());

//error_log(print_r($request,true));




$signature = $_SERVER['HTTP_SIGNATURE'];
//keyId="https://mastodon.social/users/haentz#main-key",algorithm="rsa-sha256",headers="(request-target) host date digest content-type",signature="Yo2XPcVMLs0qUhW59dlvRE8b9bXhm0kJjPPSx3prpCsbtkh9+mPt5DAK5q3j5Ro7RL7j6uNOypdfVvVlx/Mc1fkx89Yy83+/qWcWku8/hjB3oIuQxTQ6wDHAT65cl5vJA3YU8BWnnzP2ks4lrm0AhH4Z7+LdYj2mztWBJN6wq7/a5ajY5dsm+DIY/mx2u9IPL8OkFbQ5K1PrqwnpRm7DM/48cF6fWsUs77CLHjr4QaQTQMfLlQjgTQqz6wJ4zNnOojvWSCZ/ZOBKfqVA6eofFlzRqVJfUq7c/B3AVUNu4G31vL/y6wx2YwI8JOxhq+BIvmiH08+Ryp4gV9mIoK1qmA=="; 
$sub =  explode(',', $signature);
$result = [];
foreach($sub as $kv) {
  list($k, $v) =  explode('=', $kv);
  $result[ $k ] = str_replace('"','',$v);
}
//error_log(print_r($result,true));


$opts = array('http' =>
  array(
   'method'  => 'GET',
    'follow_location' => true,
    'max_redirects' => 20,
    'timeout' => 10

  )

);

                        

$context  = stream_context_create($opts);


  $key = file_get_contents($result['keyId'], false, $context);

  error_log($key);


//https://rhiaro.co.uk/2016/05/minimal
// https://knuspermagier.de/posts/2022/der-kirby-blog-als-fediverse-teilnehmer-in-vierhundert-einfachen-schritten
/**
 * Follow request
 */
if($request->type=="Follow") {


}

// part of the actiovity pub  server API
//follow -> save to db
/**
 * 
 * HTTP headers:
X-Forwarded-Proto: https
X-Forwarded-For: 162.55.173.236
Signature: keyId="https://mastodon.social/users/unoceanodefuego#main-key",algorithm="rsa-sha256",headers="(request-target) host date digest content-type",signature="acgDDCcE3nrgO2Qr2gco6NniJ4va3FoRwuYLUtrjiRwl67ErE5KLqy3DZ1eW2SVV/LCZJwE/09PvH0YBYf6E+aTPCZYFf5vPHL984bC2bW6SniV16o6hwP7ntv0hdUJJZK+thxpi3Ux2i2rgBAScRUfIpW2qLXV22e/mZ2TsCAN0BfyCm3xMyUrAvCNAtiiIGxt1S/iCKEjOHfrDlU7azLBS2KIYy7yEy3g95oiDjobXmQnP+RQ1GW4Qt7u5zpF7uuU0uWsPzcdAfSLbWerl1or3n4mV4pzFXiZqUd6rhAeR0qjQhQ2HqDp3zOXX4+HpQ8JzQZndcQQ86YUlClEVKw=="
Digest: SHA-256=IaCMWTgWFQScjXZLPGyS8H3kpdglj2Ddwu1npssKde0=
Date: Mon, 03 Jul 2023 13:41:50 GMT
Content-Type: application/activity+json
Accept-Encoding: gzip
Content-Length: 799
User-Agent: http.rb/5.1.1 (Mastodon/4.1.2+nightly-20230627; +https://mastodon.social/)
Host: f9f5-95-89-45-59.ngrok-free.app
 * 
 * body
 * search:
 *  
 * 
 * follow:
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