<?php
$servername = "db";
$username = "newsletter";
$password = "N3w#l3dd3r!";
$dbname = "firms";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
