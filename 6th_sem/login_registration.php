<?php
session_start();

// Include PHPMailer classes
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database configuration
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'salon_db';  // Updated to use salon_db

// Create connection
$con = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// ## for login

if (isset($_POST['login'])) {
    $emailUsername = $_POST['email_username'];
    $password = $_POST['password'];
    $userType = $_POST['user_type'];

    // Updated query for checking username or email in salon_db
    $query = "SELECT * FROM `registered_users` WHERE `username`='$emailUsername' OR `email`='$emailUsername'";
    $result = mysqli_query($con, $query);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $result_fetch = mysqli_fetch_assoc($result);
            if (password_verify($password, $result_fetch['password'])) {
                // Check if the user is attempting to log in as admin
                if ($userType == 'admin' && $result_fetch['email'] != 'salon@gmail.com') {
                    echo "<script>
                            alert('You are not an admin.');
                            window.location.href='login.php';
                          </script>";
                } else {
                    if ($userType == 'admin' && $result_fetch['email'] == 'salon@gmail.com') {
                        // Admin login
                        $_SESSION['logged_in'] = true;
                        $_SESSION['username'] = $result_fetch['username'];
                        header("location: ad_admin.php");
                    } else {
                        // Customer login (default)
                        $_SESSION['logged_in'] = true;
                        $_SESSION['username'] = $result_fetch['username'];
                        header("location: home.php");
                    }
                }
            } else {
                // Incorrect password
                echo "<script>
                        alert('Incorrect password');
                        window.location.href='login.php';
                      </script>";
            }
        } else {
            // Email or username not registered
            echo "<script>
                    alert('Email or username not registered');
                    window.location.href='login.php';
                  </script>";
        }
    } else {
        // Cannot run query
        echo "<script>
                alert('Cannot run query');
                window.location.href='index.php';
              </script>";
    }
}

// Function to generate OTP
function generateOTP() {
    return rand(100000, 999999); // Generate a 6-digit OTP
}

// Function to send OTP to user's email
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ghimireaadesh2003@gmail.com';
    $mail->Password = 'pwoquzmnuccdynwv';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465; // 465 for SSL

    // Email content
    $mail->setFrom('ghimireaadesh2003@gmail.com', 'Aadesh Ghimire'); // Update with your Gmail email address and your name
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'OTP Verification';
    $mail->Body = "Your OTP for registration is: $otp";

    // Send email
    try {
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return false; // Error sending email
    }
}

// Handle registration form submission
if (isset($_POST['register'])) {
    // Check if the username or email already exists in the table
    $username = $_POST['username'];
    $email = $_POST['email'];
    $user_exist_query = "SELECT * FROM `registered_users` WHERE `username`='$username' OR `email`='$email'";
    $result = mysqli_query($con, $user_exist_query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $result_fetch = mysqli_fetch_assoc($result);
            if ($result_fetch['username'] == $username) {
                // Error for username already taken
                echo "<script>
                    alert('$username - Username already taken');
                    window.location.href='index.php';
                </script>";
            } else {
                // Error for email already taken
                echo "<script>
                    alert('$email - E-mail already taken');
                    window.location.href='index.php';
                </script>";
            }
        } else {
            // Generate OTP
            $otp = generateOTP();

            // Send OTP to user's email
            if (sendOTP($email, $otp)) {
                // Store OTP in session for validation
                $_SESSION['otp'] = $otp;
                // Store registration data in session
                $_SESSION['registration_data'] = $_POST;
                // Redirect to OTP verification page
                header("Location: otp_verification.php");
                exit();
            } else {
                // Failed to send OTP
                echo "<script>
                    alert('Failed to send OTP. Please try again later.');
                    window.location.href='index.php';
                </script>";
            }
        }
    } else {
        // Cannot run query
        echo "<script>
            alert('Cannot Run Query');
            window.location.href='index.php';
        </script>";
    }
}

// Close the database connection
mysqli_close($con);
?>
