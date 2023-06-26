<?php
//submitted from join form?
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once('include/db.inc.php');


$message= "";

// submitted?
$join = $_POST["Join"];
if($join=="submit") {

// validate form data
$email = strtolower(trim($_POST['email']));
$name = strtolower(trim($_POST['name']));

if($name==""   || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $message = "Email or Name not valid.";
} else {

  require_once('include/db_tuser.inc.php');
  
  $user = $orm->create(User::class);
  
  $user =  $orm(User::class)->where('email')->is($email)->get();

  $error = 0;
  // test if in db
  if($user!=null) {
  $error = 1;
    $message = "Email already registered. <a href=\"login.php\">Login</a>?<br>";
  }
  
  $user =  $orm(User::class)->where('name')->is($name)->get();

  if($user!=null) {
    $error = 1; 
    $message .= "Username already registered.<br>"; 
  }

  if($error==0){
    $user = $orm->create(User::class);
   // todo: better rresgistration process. set in db: user_email_confirmed=0, do not send link to login, but to confirm&login in first registratioon email,. If user doesnt click within 24h, remove from db
   
    // Set user's name
    $user->setEmail($email);  
    $user->setName($name);
    $user->setCreationdate(new DateTime());
    $orm->save($user);
    
    $logintoken = hash('ripemd128', "saltlogin".$iduser.time()); 
    $user->setLogintoken($logintoken);
    $user->setLogintokencreationdate(new DateTime());
    $orm->save($user);
    $message = "<b>An Email has been sent to your registered Email Adress with a link to log in.</b>";


//todo: put duplicate code, put in include


$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Mailer = "smtp";
$mail->SMTPDebug  = 0;  
$mail->SMTPAuth   = TRUE;
$mail->SMTPSecure = "tls";
$mail->Port       = 587;
$mail->Host       = "smtp.gmail.com";
$mail->Username   = $gmailEmail;
$mail->Password   = $gmailPassword;
$mail->IsHTML(false);
$mail->AddAddress($user->getEmail(), "recipient-name");
$mail->SetFrom("hans@hans-schneider.de", "from-name");
$mail->Subject = "Login to Fedifit";
$content = "Your login to Fedifit: http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]."/login_do.php?t=".$user->getLogintoken();
// todo: change to rewrite: /login/0abcd... 

$mail->MsgHTML($content); 
if(!$mail->Send()) {
 // echo "Error while sending Email.";
 // var_dump($mail);
} else {
 // echo "Email sent successfully";
}



  } else {

//$message .= "somethign went wrong";

  }



}

}



?>

<!DOCTYPE html>
<html>
<body>
<?php include("include/nav.inc.php") ?>
<?= $message ?>
<form action="/" method="post" enctype="multipart/form-data">
  Join:  <br>
  <label for="email">Email:
  <input type="text" name="email" id="email">
  </label>

  <br>

  <label for="name">Nickname:
  <input type="text" name="name" id="name">
  </label>
  
  <br>
  
  <input type="submit" value="submit" name="Join">
</form>

</body>
</html>