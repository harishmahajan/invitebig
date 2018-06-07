<?php
	 $servername = "localhost";
	 $username = "root";
	 $password = "W2GC@zF!6c7%";
	 $dbname = "invitebignew";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "INSERT INTO temptbe (LastName, FirstName) VALUES ('John', 'Doe')";
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>