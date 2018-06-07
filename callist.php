<?php
session_start();
require_once("config.php");
$conn = new mysqli($servername, $username, $password, $dbname);

$data=array();
$i=0;
$flag=0;
//$sqlSelect = "SELECT * FROM googleResourcelist gr where userid = ".$_SESSION['userid'];

$sqlSelect =  "SELECT googleCallist.calName as calName, gr.resourceid as resourceid FROM googleResourcelist gr 
    INNER JOIN googleCallist ON gr.calid = googleCallist.calid and gr.userid = ".$_SESSION['userid'];

    //echo $sqlSelect;

// $resultSelect = $conn->query($sqlSelect);
//   if ($resultSelect->num_rows > 0) {
//       while($row = $resultSelect->fetch_assoc()) {
//         // if($_POST['rids']==$row['resourceid'] )
//         // {

//         // }
//         $data["name"][$i] = $row['calName'];
//         $data["resourceid"][$i] = $row['resourceid'];
//         if($_POST['rids']==$row['resourceid'])
//         {
//           $data["temp"][$i] = 1;
//         }
//         else
//         {
//           $data["temp"][$i] = 0;
//         }
//         $i++;
//         $flag=1;
//       }
//   }
//  $conn->close();
//  if($flag==1)
//  print_r(json_encode($data));

$data=array();
$i=0;
$flag=0;
$selectCal="";

if($_POST['rids']!='')
{
  $sqlGrSelect = "SELECT * FROM googleResourcelist where userid = ".$_SESSION['userid']." and resourceid = ".$_POST['rids'];
  $resultGrSelect = $conn->query($sqlGrSelect);
   if ($resultGrSelect->num_rows > 0) {
        while($rows = $resultGrSelect->fetch_assoc()) {
          $selectCal=$rows['calid'];
        }
      }
}

$sqlSelect = "SELECT * FROM googleCallist where userid = ".$_SESSION['userid'];
$resultSelect = $conn->query($sqlSelect);
  if ($resultSelect->num_rows > 0) {
      while($row = $resultSelect->fetch_assoc()) {
        $data["name"][$i] = $row['calName'];
        $data["cid"][$i] = $row['calid'];
        // $data["status"][$i] = $row['status'];
        // $data["resourceid"][$i] = $row['resourceid'];
        if($selectCal==$row['calid'])
        {
          $data["temp"][$i] = 1;
        }
        else
        {
          $data["temp"][$i] = 0;
        }
        $i++;
        $flag=1;
      }
  }
 $conn->close();
 if($flag==1)
 print_r(json_encode($data));
 
?>