<?php
 ob_start();
 session_start();
 require_once("config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sqlSelect = "SELECT * FROM iframe_header where venueid = ". $_SESSION["venueids"];
$resultSelect = $conn->query($sqlSelect);
	if ($resultSelect->num_rows > 0) {
	    while($row = $resultSelect->fetch_assoc()) {
	    	echo $row['isdisplay'];
	    	break;
	    }
	}
	$conn->close();
?> 