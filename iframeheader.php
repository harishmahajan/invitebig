<?php
session_start();
ob_start();
require_once("config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sqlSelect = "SELECT * FROM iframe_header where venueid = ". $_SESSION["venueids"];
$resultSelect = $conn->query($sqlSelect);

if(isset($_POST['checkdata']))
{
	if($_POST['checkdata']=="checked")
	{
		$status='show';
	}
	else
	{
		$status='hide';
	}
	if ($resultSelect->num_rows > 0) {
	    while($row = $resultSelect->fetch_assoc()) {
	        $sqlUpdate = "UPDATE iframe_header SET isdisplay='".$status."' where venueid = ". $_SESSION["venueids"];
				if (mysqli_query($conn, $sqlUpdate)) {
				    //echo $status;
				}
				else
				{
					//echo $sqlUpdate;
				}
	    }
	} else {
		$sqlInsert = "INSERT INTO iframe_header (venueid, isdisplay)
		VALUES (".$_SESSION["venueids"].',"show"'.")";

		if ($conn->query($sqlInsert) === TRUE) {
		    return true;
		}    
	}
}


$conn->close();
?> 