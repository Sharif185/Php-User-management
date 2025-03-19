<?php
$servername = "localhost";
$username = "root"; // Default for XAMPP/WAMP
$password = ""; // Default for XAMPP/WAMP
$database = "user_management";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
