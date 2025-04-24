<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
$database = 'salon_db';

// Create a new mysqli connection
$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check for overlapping bookings
function hasOverlappingBookings($conn, $serviceId, $bookingDateTime) {
    $sql = "SELECT COUNT(*) AS count FROM bookings 
            WHERE service_id = ? 
            AND booking_time = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $serviceId, $bookingDateTime);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result !== false) {
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    } else {
        echo "ERROR: Could not execute query: $sql. " . $conn->error;
        return false;
    }
}

// Function to book a service
function bookService($conn, $serviceId, $userId, $name, $phone, $bookingDateTime, $notes) {
    // Validate input data
    if (empty($bookingDateTime)) {
        echo "<script>alert('ERROR: Booking date and time are required.');</script>";
        return;
    }
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        echo "<script>alert('ERROR: Phone number must be a 10-digit number.'); window.location.href = 'service_details.php?id=$serviceId';</script>";
        return;
    }

    // Check for overlapping bookings
    if (hasOverlappingBookings($conn, $serviceId, $bookingDateTime)) {
        echo "<script>alert('Sorry! The service is already booked for the selected time.'); window.location.href = 'service_details.php?id=$serviceId';</script>";
        return;
    }

    // Insert booking details into the database
    $sql = "INSERT INTO bookings (service_id, user_id, booking_date, booking_time, name, phone, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iisssss", $serviceId, $userId, $bookingDateTime, $bookingDateTime, $name, $phone, $notes);
        if ($stmt->execute()) {
            // Send confirmation email to the user
            $mail = new PHPMailer(true);
            // Configure SMTP settings (replace with your actual details)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ghimireaadesh2003@gmail.com'; 
            $mail->Password = 'pwoquzmnuccdynwv';    
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465; // 465 for SSL
            
            // Send confirmation email to the user
            $recipientEmail = $_SESSION['email']; // Assuming user's email is in the session
            $mail->setFrom('your_email@gmail.com', 'Salon Booking Service');
            $mail->addAddress($recipientEmail, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Service Booking Confirmation';
            
            // Retrieve additional information about the booked service
            $serviceInfoSql = "SELECT name AS service_name FROM services WHERE id = ?";
            $serviceInfoStmt = $conn->prepare($serviceInfoSql);
            $serviceInfoStmt->bind_param("i", $serviceId);
            $serviceInfoStmt->execute();
            $serviceInfoResult = $serviceInfoStmt->get_result();
            $serviceInfo = $serviceInfoResult->fetch_assoc();
            $serviceName = $serviceInfo['service_name'];

            // Include service information in the email body
            $mail->Body = "Dear $name,<br>Your booking for the service $serviceName has been confirmed!<br>Booking Date and Time: $bookingDateTime<br>Thank you for choosing our salon.";

            try {
                $mail->send();
                echo "<script>alert('Your booking has been confirmed! Confirmation email sent.'); window.location.href='home.php';</script>";
            } catch (Exception $e) {
                echo "Error sending email: " . $mail->ErrorInfo;
            }
        } else {
            echo "ERROR: Could not execute query: $sql. " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "ERROR: Could not prepare query: $sql. " . $conn->error;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Retrieve input data from the form and validate
    $serviceId = $_POST['service_id'] ?? null;
    $userId = $_SESSION['user_id'];
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $serviceDate = $_POST['service_date'] ?? '';  // Booking Date (YYYY-MM-DD)
    $serviceTime = $_POST['service_time'] ?? '';  // Booking Time (HH:MM)
    $notes = $_POST['notes'] ?? '';

    // Check if booking date and time are set
    if (!$serviceDate || !$serviceTime) {
        echo "<script>alert('ERROR: Service date and time are required.'); window.location.href = 'service_details.php?id=$serviceId';</script>";
    } else {
        // Combine date and time into a DATETIME format
        $bookingDateTime = $serviceDate . ' ' . $serviceTime;  // Format: YYYY-MM-DD HH:MM

        // Call the bookService function to process the booking
        bookService($conn, $serviceId, $userId, $name, $phone, $bookingDateTime, $notes);
    }
}

// Close the database connection
$conn->close();
?>
