<?php
session_start();
if(!isset($_SESSION['iduser'])) {
    header('Location: /');
    die();
}
 
//Abfrage der Nutzer ID vom Login
$iduser = $_SESSION['iduser'];
?>