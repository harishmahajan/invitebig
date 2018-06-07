<?php

	// require_once("functions.php");
require_once("../config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
$sql = "SELECT * FROM booking where cal_id = "."'".$_POST['calid']."'";
$result = $conn->query($sql);
echo "\r\n ".$result->num_rows."=>".$sql;
if ($result->num_rows > 0) {

		 while($row = $result->fetch_assoc()) {
         	//if($row["cal_updatedate"]!=$_POST['calupdatedate'])
         	$sqlBookingUpdate = "";
       		$sqlBookingUpdate = "UPDATE booking SET userid = ".$_SESSION['userid'].", start = ".$_POST['st'].", stop = ".$_POST['endt'].", cal_updatedate = ".$_POST['calupdatedate']." WHERE bookingid = ".$row['bookingid'];

			mysqli_query($conn, $sqlBookingUpdate);

         	$sqlBookingResourcesUpdate = "";
       		$sqlBookingResourcesUpdate = "UPDATE booking_resources SET starttime=".$_POST['st'].", stoptime = ".$_POST['endt']." WHERE bookingid = ".$row['bookingid'];

			mysqli_query($conn, $sqlBookingResourcesUpdate);

         	$sqlBookingInfoUnpdate = "";
       		$sqlBookingInfoUnpdate = "UPDATE booking_info SET name="."'".$_POST['eventname']."'".", description = "."'".$_POST['description']."'".", contact_email = "."'".$_POST['contact_email']."'"." WHERE bookingid = ".$row['bookingid'];

			mysqli_query($conn, $sqlBookingInfoUnpdate);

    	 }

	 
} else {

echo "Insert...\r\n";
 //  	$status = "Imported"; 	
	// $sqlBooking = "INSERT INTO booking (venueid,userid,timestamp,start,stop,status,cal_updatedate,cal_id,isgooglesync,iseventcreated) VALUES (".$_SESSION['venueids'].",".$_SESSION['userid'].","."'".$_POST['todaysdate']."'".","."'".$_POST['st']."'".","."'".$_POST['endt']."'".","."'".$status."'".",".$_POST['calupdatedate'].","."'".$_POST['calid']."'".","."'true'".","."'true'".")";

	// $conn->query($sqlBooking);

	// $query="";
	// $query = "SELECT MAX(bookingid) FROM booking";
	// $result = mysqli_query($conn,  $query);
	// $row = mysqli_fetch_row($result);
	// $bid = $row[0];

	// $sqlBooking_resources = "INSERT INTO booking_resources (bookingid,venueid,starttime,stoptime,resourceid) VALUES (".$bid .",".$_SESSION['venueids'].","."'".$_POST['st']."'".","."'".$_POST['endt']."'".",".$_POST['resourceid'].")";

	// $conn->query($sqlBooking_resources);

	// $evname=utf8_encode($_POST['eventname']);
	// $desc=utf8_encode($_POST['description']);

	// $sqlBooking_infoInsert = "INSERT INTO booking_info (bookingid,name,description,contact_email) VALUES (".$bid.","."'".$evname."'".","."'".$desc."'".","."'".$_POST['contact_email']."'".")";
	// $conn->query($sqlBooking_infoInsert); 
}


?>