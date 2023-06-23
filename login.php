<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once('include/db.inc.php');
session_start();

// todo: in include!
if(isset($_SESSION['iduser'])) {
    header('Location: /members/');
    die();
}
 
$message= "";

// submitted?
$login = $_POST["Login"];
if($login=="submit") {
    //require DB stuff
    require_once('include/db_tuser.inc.php');
    require_once("../include/DO_NOT_DEPLOY.php");
    $user = $orm->create(User::class);

    $email = $_POST["email"];

    // get iduser by email
    $user =  $orm(User::class)->where('email')->is($email)->get();
    if($user) {

   
    $iduser = $user->getId();
    


    $logintoken = hash('ripemd128', "saltlogin".$iduser.time());
   

    $user->setLogintoken($logintoken);
    $user->setLogintokencreationdate(new DateTime());
    $orm->save($user);
    
    
    // insert logintoken + date in db
    // send login email

    } else {

        // email not found error message!

    }
    
    $message = "<b>An Email has been sent to your registered Email Adress with a link to log in.</b>";

   


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

}

?><!DOCTYPE html>
<html>
<body>
<?php include("include/nav.inc.php") ?>
<?= $message ?>
<form action="login.php" method="post" enctype="multipart/form-data">
  Login:  <br>
  <label for="email">Email:
  <input type="text" name="email" id="email">
  </label>

  <br>

  <input type="submit" value="submit" name="Login">
</form>

</body>
</html>

