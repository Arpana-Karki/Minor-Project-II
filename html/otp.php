<?php
// otp.php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];

    // Generate OTP
    $otp = rand(100000, 999999);

    // Store OTP in session for verification later
    $_SESSION['otp'] = $otp;
    $_SESSION['phone'] = $phone;

    // In real-world scenarios, you would send the OTP via SMS service (like Twilio)

    echo "OTP Sent: $otp <br><a href='verify-otp.php'>Go to OTP verification</a>";
}
?>
