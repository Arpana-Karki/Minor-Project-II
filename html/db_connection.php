<?php
// db_connection.php

// Database credentials
$servername = "localhost";  // or your host (e.g., '127.0.0.1' or 'localhost')
$username = "your_db_username";  // your database username
$password = "your_db_password";  // your database password
$dbname = "your_db_name";  // your database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // If there's an error, display it and stop the script
    die("Connection failed: " . $conn->connect_error);
}

// Connection was successful, no need for further actions here
?>
