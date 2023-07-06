<?php 
require_once('../include/db.inc.php');
require_once($basedir.'/include/lib_helper.inc.php');

/*

The request data for this example"

HTTP request:
POST /inbox/haentz HTTP/1.1

HTTP headers:
X-Forwarded-Proto: https
X-Forwarded-For: 167.235.253.16
Signature: keyId="https://mastodon.social/users/haentz#main-key",algorithm="rsa-sha256",headers="(request-target) host date digest content-type",signature="pcnx7L4JnqOecwHRIHr4AcQzrDQGJITiKi4obFCMN47ET17Nukyej4tc9ctwuf42osDJBHZFFeHDHq/ersnH/wzmmSYgKb2n/pjZlIIiKbFpP8uZWR4occ3ef5yw0h/DK9q4iRtxRefTyAj6uXAc9M/HTil5r2Mf7vKAtYxN0VlEowokkLmZv2QWUQfnLu0KS6GX1NsD+JPd2mhuHDl5f3blIV3cNFOL4dvAeagCoA6gScfoO9GvgWWKHMyjbzBO8uJoGiA/dQ78rnd6ooPX/bOlhhIwBXZQJcWUt03DQd8hgX1631xnmF7WqVH87Omtg3/1J9UEmP+Q4jvRReaZbg=="
Digest: SHA-256=7ra3kP4ddU4a8Q0Rg/xcGoebr05AkYTzSHfpVW8Pj6o=
Date: Wed, 05 Jul 2023 12:59:35 GMT
Content-Type: application/activity+json
Accept-Encoding: gzip
Content-Length: 246
User-Agent: http.rb/5.1.1 (Mastodon/4.1.2+nightly-20230705; +https://mastodon.social/)
Host: f9f5-95-89-45-59.ngrok-free.app
*/

use phpseclib3\Crypt\RSA;

$actorKey="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4SEtohtPowClx878glaG
OmUkM0HD+vM0M9fH1opKG8mnlplfO3vuQk5iN8Jp5Sg4DD6lzHnbQ1Ze57uVm90I
R4DgEdRidaZs1NPrYPv63j1r3HMoBruPw0EX+/paf8izOcxxx6B7G2ebBl/rZQbI
UtAsBLYFM+uHWbMR8+H5hHlhuRF/SHHRmWvNAeGm9B1H6uZMt3chyPluc/t3Kxz9
4/qE52b2YM1YBhzL+50eTpHWq6RaMEQ8zVCXKi1+e7Gxdf++EFJFgx3PR1x9U3AM
AoT4UyZzzMX5jVkpi1G5P+/2MnBv3DsF95K9b5A+Y15bqCQCfkgjcfDHENuADHYa
kQIDAQAB
-----END PUBLIC KEY-----
";

$signatureBase = "(request-target): post /inbox/haentz
host: f9f5-95-89-45-59.ngrok-free.app
date: Wed, 05 Jul 2023 12:59:35 GMT
digest: SHA-256=7ra3kP4ddU4a8Q0Rg/xcGoebr05AkYTzSHfpVW8Pj6o=
content-type: application/activity+json
";

$sig="pcnx7L4JnqOecwHRIHr4AcQzrDQGJITiKi4obFCMN47ET17Nukyej4tc9ctwuf42osDJBHZFFeHDHq/ersnH/wzmmSYgKb2n/pjZlIIiKbFpP8uZWR4occ3ef5yw0h/DK9q4iRtxRefTyAj6uXAc9M/HTil5r2Mf7vKAtYxN0VlEowokkLmZv2QWUQfnLu0KS6GX1NsD+JPd2mhuHDl5f3blIV3cNFOL4dvAeagCoA6gScfoO9GvgWWKHMyjbzBO8uJoGiA/dQ78rnd6ooPX/bOlhhIwBXZQJcWUt03DQd8hgX1631xnmF7WqVH87Omtg3/1J9UEmP+Q4jvRReaZbg==";

$rsa = RSA::createKey()
->loadPublicKey($actorKey)
->withHash('sha256'); 

error_log($rsa->verify( $signatureBase,  base64_decode($sig, true))?"Y":"N");

die;


$request = json_decode(file_get_contents('php://input'));
// filter spam calls
if($request->actor!='https://mastodon.social/users/haentz') die;

  // foreach ($_SERVER as $name => $value) { error_log($name .":".$value);   }
//error_log('inbox request dump: '.(new DumpHTTPRequestToFile)->execute());

//error_log(print_r($request,true));
error_log('inbox request dump: '.(new DumpHTTPRequestToFile)->execute());




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
   'header'=>"Accept: application/activity+json"
  //  ,
  //   'follow_location' => true,
  //   'max_redirects' => 20,
  //   'timeout' => 10

  )

);

// (request-target) post /inbox/haentz
// host: f9f5-95-89-45-59.ngrok-free.app
// date: Wed, 05 Jul 2023 12:13:33 GMT
// digest: SHA-256=jmcNJqxWhO1LNZxDOYrTwhbuwLuvtUCPUHzVrkJ8kBk=
// content-type: application/activity+json



    $actorFile = explode('#',$result['keyId'])[0];
   //$result['keyId']
  $context  = stream_context_create($opts);
  $actorJson = file_get_contents($actorFile, false, $context);
  $actor = json_decode($actorJson,true);
  //  error_log(print_r($actor, true));
  
$actorKey = $actor['publicKey']['publicKeyPem'];
$request_target = strtolower($_SERVER['REQUEST_METHOD']).' '.$_SERVER['REQUEST_URI'];
$signatureHeaders = explode(" ",$result['headers']);
$signatureBase = '';

foreach($signatureHeaders as $signatureHeader) {

  if($signatureHeader=='(request-target)') {
    $signatureBase.='(request-target) '.$request_target."\n";
  } else {
    $signatureBase.=$signatureHeader.': '.$_SERVER['HTTP_'.strtr(strtoupper($signatureHeader),'-','_')]."\n";
  }
 
}

//  error_log('sig: '.$result['signature']); 
//  error_log('digest '.openssl_digest($signatureBase,'sha256'));
$decrypted = '';
   // Verify that string using the public key and the original 
        // signature.
        $rsa = RSA::createKey()
                  ->loadPublicKey($actorKey)
                  ->withHash('sha256'); 

// error_log(':'.$actorKey.':'); 

error_log(':'.$signatureBase.':'); 

// error_log(':'.$result['signature'].':'); 


//error_log(':'.base64_decode($result['signature'], true).':'); 


error_log($rsa->verify( $signatureBase,  base64_decode($result['signature'], true))?"Y":"N");

 //error_log('original :'.openssl_decrypt($result['signature'],$actorKey)));



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
Signature: keyId="https://mastodon.social/users/haentz#main-key",algorithm="rsa-sha256",headers="(request-target) host date digest content-type",signature="acgDDCcE3nrgO2Qr2gco6NniJ4va3FoRwuYLUtrjiRwl67ErE5KLqy3DZ1eW2SVV/LCZJwE/09PvH0YBYf6E+aTPCZYFf5vPHL984bC2bW6SniV16o6hwP7ntv0hdUJJZK+thxpi3Ux2i2rgBAScRUfIpW2qLXV22e/mZ2TsCAN0BfyCm3xMyUrAvCNAtiiIGxt1S/iCKEjOHfrDlU7azLBS2KIYy7yEy3g95oiDjobXmQnP+RQ1GW4Qt7u5zpF7uuU0uWsPzcdAfSLbWerl1or3n4mV4pzFXiZqUd6rhAeR0qjQhQ2HqDp3zOXX4+HpQ8JzQZndcQQ86YUlClEVKw=="
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