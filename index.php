<?php
$pagetitle = "Welcome to Orple Bank";
require_once('assets/header.php');
$code = rand(100000, 999999);
// echo $code;
// From current time
$date = new DateTime();
$date->setTimezone(new DateTimeZone('Africa/Lagos'));
// Add 30 minutes to the current time
$expired = $date->modify('+30 minutes')->format('Y-m-d H:i:s');


?>