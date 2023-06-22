<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tumutugma";
$charset = "utf8mb4";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>