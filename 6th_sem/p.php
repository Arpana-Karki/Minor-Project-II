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

// Function to get all services
function getAllServices() {
    global $conn;
    $services = array();

    $sql = "SELECT * FROM services";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }
    return $services;
}

// Function to get all users' emails
function getAllUserEmails() {
    global $conn;
    $emails = array();

    $sql = "SELECT email FROM registered_users";  // Assuming registered_users table for emails
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $emails[] = $row['email'];
        }
    }
    return $emails;
}

// Send promotional email to all users
function sendPromotionMail($selectedServiceId) {
    global $conn;

    // Get service details
    $serviceSql = "SELECT * FROM services WHERE id = ?";
    $stmt = $conn->prepare($serviceSql);
    $stmt->bind_param("i", $selectedServiceId);
    $stmt->execute();
    $serviceResult = $stmt->get_result();
    $service = $serviceResult->fetch_assoc();

    // Get all user emails
    $userEmails = getAllUserEmails();

    // Create the email content
    $subject = "Special Promotion on " . $service['name'];
    $body = "Dear Customer,<br><br>We are offering a special promotion on our " . $service['name'] . " service!<br><br>" . $service['description'] . "<br><br>Don't miss out on this limited-time offer!<br><br>Book your appointment today!<br><br>Best regards,<br>Your Salon Team";

    // Send the email to all users
    $mail = new PHPMailer(true);
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ghimireaadesh2003@gmail.com'; // Replace with your email
    $mail->Password = 'pwoquzmnuccdynwv'; // Replace with your email password
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465; // 465 for SSL

    $mail->setFrom('your_email@gmail.com', 'SalonX'); // Replace with your email and salon name
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;

    // Send email to each user
    foreach ($userEmails as $email) {
        $mail->clearAddresses();
        $mail->addAddress($email);
        try {
            $mail->send();
        } catch (Exception $e) {
            echo "Error sending email to $email: " . $mail->ErrorInfo . "<br>";
        }
    }
    echo "<script>alert('Promotional emails sent successfully!'); window.location.href = 'ad_admin.php';</script>";
}

// Handle form submission for sending promotion mail
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_promotion'])) {
    $selectedServiceId = $_POST['service_id'] ?? null;
    if ($selectedServiceId) {
        sendPromotionMail($selectedServiceId);
    } else {
        echo "<script>alert('Please select a service to send the promotion.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Promotion Mail</title>
</head>
<body>
    <nav>
        <div style="position: absolute; top: 0; left: 0; padding: 10px; ">
            <img src="logo.png" alt="Logo" style="height: 50px;">
        </div>
        <ul>
            <li><a href="ad_admin.php" class="active">Home</a></li>
            <li><a href="view_booking.php" class="active">View Bookings</a></li>
           
            <li style="list-style: none; display: inline; float: right; margin-left: 10px;">
  <a href="ad_admin.php" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Go back</a>
</li>
<li style="list-style: none; display: inline; float: right;">
  <a href="logout.php" style="background-color: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Logout</a>
</li>

        </ul>
    </nav>

    <div style="text-align: center; font-size: 24px; margin-top: 50px;">
        <h2>Select a Service to Send Promotion</h2>
        <form method="POST" action="" style="display: inline-block; text-align: left;">
            <label for="service_id" style="font-size: 18px;">Select Service:</label>
            <select name="service_id" id="service_id" required style="font-size: 16px; padding: 10px; margin-top: 10px; width: 200px;">
                <option value="">--Select Service--</option>
                <?php
                // Fetch all services
                $services = getAllServices();
                foreach ($services as $service) {
                    echo "<option value='" . $service['id'] . "'>" . $service['name'] . "</option>";
                }
                ?>
            </select>
            <br><br>
            <button type="submit" name="send_promotion" style="font-size: 18px; background-color: coral; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                Send Promotion Mail
            </button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 50px; font-size: 20px;">
        <h3>Registered Users:</h3>
        <table border="1" cellpadding="10" cellspacing="0" style="margin: 0 auto; width: 80%; text-align: left;">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
            </tr>
            <?php
            // Fetch and display registered users
            $sql = "SELECT * FROM registered_users";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['full_name'] . "</td>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>
    </div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
