<?php
// signup.php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Connect to the database (add your database credentials)
    $conn = new mysqli('localhost', 'root', '', 'service_provider');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Store user details in the database
    $stmt = $conn->prepare("INSERT INTO users (name, phone, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $password);
    $stmt->execute();

    // Optionally, save the user in a session
    $_SESSION['phone'] = $phone;

    echo "Account created successfully! <br><a href='login.html'>Go to Login</a>";
    $stmt->close();
    $conn->close();
}
?>
