<?php 
//Database configuration for Toko

$host = "localhost";
$user = "root";
$password = "";
$dbname = "toko1";

//Create connection using MySQLi
$conn = new mysqli($host,$user,$password,$dbname);

//Check connection
if ($conn->connect_error){
  die("Connection failed:" . $conn->connect_error);
}
?>