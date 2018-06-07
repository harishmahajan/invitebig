<?php
require_once("../config.php");
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();

//$sql = "SELECT booking.bookingid as bookingid,  booking.cal_id as cal_id, booking.start as start, booking.stop as stop, booking.cal_updatedate as cal_updatedate, booking_info.name as name FROM booking INNER JOIN booking_info ON booking.bookingid=booking_info.bookingid and booking.start >= ".$_POST['todaysdate'];

$sql = "SELECT booking.bookingid AS bookingid, venues.timezone AS timezone, booking.cal_id AS cal_id, booking.start AS 'start', booking.stop AS 'stop', booking.cal_updatedate AS cal_updatedate, booking_info.name AS name, booking_info.description AS description, booking_info.contact_email AS contact_email, venues.venueid AS venueid, venues.address AS address
	FROM booking
	INNER JOIN booking_info ON booking.bookingid = booking_info.bookingid
	INNER JOIN booking_resources ON booking.bookingid = booking_resources.bookingid
	INNER JOIN venues ON booking_resources.venueid = venues.venueid
	AND booking.start >=".$_POST['todaysdate']." AND booking.iseventcreated!='true'";

// SELECT booking.bookingid AS bookingid, venues.timezone AS timezone, booking.cal_id AS cal_id, booking.start AS 'start', booking.stop AS 'stop', booking.cal_updatedate AS cal_updatedate, booking_info.name AS name
// FROM booking
// INNER JOIN booking_info ON booking.bookingid = booking_info.bookingid
// INNER JOIN booking_resources ON booking.bookingid = booking_resources.bookingid
// INNER JOIN venues ON booking_resources.venueid = venues.venueid
// booking.start >=1467691620
// LIMIT 0 , 30

echo $sql;
$result = $conn->query($sql);
$response=array();
$data=array();

if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {

			// if($row['address']=='' || $row['address']==null)
			// 	$address=" ";
			// else
				$address=utf8_encode($row['address']);

			// if($row['description']=='' || $row['description']==null)
			// 	$description=" ";
			// else
				$description=utf8_encode($row['description']);

			// if($row['name']=='' || $row['name']==null)
			// 	$eventname=" ";
			// else
				$eventname=utf8_encode($row['description']);

			// if($row['contact_email']=='' || $row['contact_email']==null)
			// 	$contactemail=" ";
			// else
				$contactemail=utf8_encode($row['contact_email']);

			$response=array("bookingid"=>$row['bookingid'],"start"=>$row['start'],"stop"=>$row['stop'],"eventname"=>$eventname,"cal_updatedate"=>$row['cal_updatedate'],"cal_id"=>$row['cal_id'],"address"=>$address,"description"=>$description,"timezone"=>$row['timezone'],"contact_email"=>$contactemail);
			//$response['name'][$i]=$row['name'];
			// $response['name'][$i]=$row['name'];
			// $response['bookingid'][$i]=$row['bookingid'];
			// $response['start'][$i]=$row['start'];
			// $response['stop'][$i]=$row['stop'];
			//if($row['name'])
			//echo $response['name'][$i]."\r\n";
			echo "\r\n".$description;	

			array_push($data,$response);
		    $sqlBookingUpdate = "UPDATE booking SET iseventcreated = 'true' WHERE bookingid = ".$row['bookingid'];

			mysqli_query($conn, $sqlBookingUpdate);


		 	// echo " \r\n== Id = ".$row['bookingid'];
		 	// echo "\r\nStart = ".$row['start'];
		 	// echo "\r\nStop = ".$row['stop'];
		 	// echo "\r\nName = ".$eventname;
		 	// echo "\r\nCal_Updated=".$row['cal_updatedate'];
		 	// echo "\r\ncontactemail=".$contactemail;
		 	// echo "\r\n timezone".$row['timezone'];
		 	// echo "\r\n address".$address;
		 	// echo "\r\n description".$description;
		 	// echo "\r\n cal_id".$row['cal_id'];

		    // if ($outp != "") {$outp .= ",";}
		    // $outp .= '{"bookingid":"'  . $row["bookingid"] . '",';
		    // $outp .= '"start":"'   . $row["start"]        . '",';
		    // $outp .= '"stop":"'   . $row["stop"]        . '",';
		    // $outp .= '"eventname":"'   . $row["name"]        . '",';
		    // $outp .= '"cal_updatedate":"'. $row["cal_updatedate"]     . '"}'; 
		 }
		 //$outp .= "]";
		 echo json_encode ( $data );
}


?>

