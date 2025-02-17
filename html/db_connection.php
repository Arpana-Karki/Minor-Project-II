<?php
$servername = "localhost"; // Change if needed
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$database = "easy_living"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
