<?php
require_once("config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
session_start();
$calendarId = $_POST['calid'];
//$sqlGoogleCallistUpdate1 = "UPDATE googleCallist SET status = 0 WHERE  userid = ".$_SESSION['userid'];
//mysqli_query($conn, $sqlGoogleCallistUpdate1);
$sqlGoogleCallistUpdate2 = "UPDATE googleCallist SET status = 1 WHERE calid = '".$calendarId."' and userid = ".$_SESSION['userid'];
mysqli_query($conn, $sqlGoogleCallistUpdate2);
//echo $sqlGoogleCallistUpdate;
?>