<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
 
$host = "localhost";
$user = "root";
$pass = "";
$db   = "diu_student_mart";
 
$conn = new mysqli($host, $user, $pass, $db);
 
if ($conn->connect_error) {
  die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}
?>