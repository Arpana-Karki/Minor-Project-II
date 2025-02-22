<?php
session_start();
require 'db_connect.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.location.href='login.html';</script>";
        exit();
    }

    // Check if the email exists
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["user_name"] = $name;
            $_SESSION["user_email"] = $email;
            echo "<script>alert('Login successful!'); window.location.href='home.html';</script>";  <!-- Redirect to home page -->
        } else {
            echo "<script>alert('Incorrect password!'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('No account found with this email.'); window.location.href='login.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
