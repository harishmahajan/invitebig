<?php

	// require_once("functions.php");
require_once("../config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create connection

// $sql = "INSERT INTO temp (tempid)
// VALUES (123)";

// if ($conn->query($sql) === TRUE) {
//     echo "New record created successfully";
// } else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
// }

// $conn->close();
	// $stmt = $GLOBALS['db']->stmt_init();

	// if ($stmt->prepare("SELECT timezone FROM venues WHERE venueid = '2'"))
	// {
	// 	//$stmt->bind_param("i",$vid);
	// 	//echo "2";
	// 	$stmt->execute();
	// 	$stmt->bind_result($tz);
	// 	if($stmt->fetch())
	// 	{
	// 		$timezone = $tz;
	// 		echo $timezone;
	// 	}
	// 	$GLOBALS['db']->CloseConn();
	// }
// echo "INSERT INTO events (event_title,starttime,stoptime) VALUES ("."'".$_POST['eventname']."'".","."'".$_POST['st']."'".","."'".$_POST['endt']."'".")";

	// $stmt = $GLOBALS['db']->stmt_init();
	// if ($stmt->prepare("INSERT INTO events (event_title,starttime,stoptime) VALUES ("."'".$_POST['eventname']."'".","."'".$_POST['st']."'".","."'".$_POST['endt']."'".")"))
	// {					
	// $stmt->bind_param($_POST['eventname'],$_POST['st'],$_POST['endt']);
		
	// 	$stmt->execute();
	// 	//$GLOBALS['db']->CloseConn();
	// }
	session_start();
	 // $sqlInsert = "INSERT INTO events (event_title,starttime,stoptime,userid,timestamp) VALUES ("."'".$_POST['eventname']."'".","."'".$_POST['st']."'".","."'".$_POST['endt']."'".","."'".$_SESSION['userid']."'".","."'".$_POST['todaysdate']."'".")";

		// if ($conn->query($sqlInsert) === TRUE) {
		//     return true;
		// }  

//echo $_SESSION['userid'];
//echo "dfdf";


	$status = "Imported"; 	
	$sqlBooking = "INSERT INTO booking (venueid,userid,timestamp,start,stop,status,cal_updatedate,cal_id) VALUES (".$_SESSION['venueids'].",".$_SESSION['userid'].","."'".$_POST['todaysdate']."'".","."'".$_POST['st']."'".","."'".$_POST['endt']."'".","."'".$status."'".",".$_POST['calupdatedate'].","."'".$_POST['calid']."'".")";

	// $sqlBooking = "INSERT INTO booking (venueid,userid,timestamp,start,stop,status) VALUES (".$_SESSION['venueids'].",".$_SESSION['userid'].",".$_POST['todaysdate'].",".$_POST['st'].",".$_POST['endt'].","."'".$status."'".")";

	$conn->query($sqlBooking);


	// if ($conn->query($sqlBooking) === TRUE) {
	//     //return true;
	// }

	$query="";
	$query = "SELECT MAX(bookingid) FROM booking";
	$result = mysqli_query($conn,  $query);
	$row = mysqli_fetch_row($result);
	$bid = $row[0];
	// echo " bid = ".$bid;
	// echo "\r\n";

	$sqlBooking_resources = "INSERT INTO booking_resources (bookingid,venueid,starttime,stoptime) VALUES (".$bid .",".$_SESSION['venueids'].","."'".$_POST['st']."'".","."'".$_POST['endt']."'".")";
	
	// $sqlBooking_resources = "INSERT INTO booking_resources (bookingid,venueid,starttime,stoptime) VALUES (".$bid.",".$_SESSION['venueids'].",".$_POST['st'].",".$_POST['endt'].")";
	$conn->query($sqlBooking_resources);
	// if ($conn->query($sqlBooking_resources) === TRUE) {
	//     //return true;
	// }
	// echo $sqlBooking . "\r\n";
	// echo $_POST['todaysdate']."\r\n";
	//   echo $_POST['endt']."\r\n";
    //      echo $_POST['st']."\r\n";
	// 	 echo $sqlBooking_resources . "\r\n";
	// echo $sqlBooking_infoInsert. "\r\n";

	$sqlBooking_infoInsert = "INSERT INTO booking_info (bookingid,name,description,contact_email) VALUES (".$bid.","."'".$_POST['eventname']."'".","."'".$_POST['description']."'".","."'".$_POST['contact_email']."'".")";
	$conn->query($sqlBooking_infoInsert);
	 	// if ($conn->query($sqlBooking_infoInsert) === TRUE) {
	 	//     //return true;
	 	// }  
	

	
		//$conn->close();
	
?>