<?php
session_start();
require_once("config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
//$data=array();
$data = "";
$i=0;
$flag=0;
//$sqlSelect = "SELECT bookingid FROM booking where userid = ".$_SESSION['userid'] . " order by DESC limit 1";
$sqlSelect = "SELECT * FROM booking WHERE userid = ".$_SESSION['userid'] . " ORDER BY bookingid DESC LIMIT 1 ";
$resultSelect = $conn->query($sqlSelect);
  if ($resultSelect->num_rows > 0) {
      while($row = $resultSelect->fetch_assoc()) {
       $data  = $row['bookingid'];
        
      }
  }
 $conn->close();

echo $data;
 
?>