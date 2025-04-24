<?php 
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'salon_db';

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get bookings for the logged-in user
function getUserBookings($userId) {
    global $conn;
    $bookings = array();

    $sql = "SELECT b.id, s.name AS service_name, s.description, s.image_path, b.booking_date, b.booking_time, b.name AS customer_name, b.phone AS customer_phone, b.notes
            FROM bookings b
            INNER JOIN services s ON b.service_id = s.id
            WHERE b.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }

    $stmt->close();
    return $bookings;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function cancelBooking($bookingId) {
    global $conn;
    $sql = "DELETE FROM bookings WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $bookingId);
        if ($stmt->execute()) {
            echo "<script>alert('Your booking has been cancelled.'); window.location.href='my_booking.php';</script>";
        } else {
            echo "ERROR: Could not execute query: $sql. " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "ERROR: Could not prepare query: $sql. " . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $bookingId = $_POST['booking_id'];
    cancelBooking($bookingId);
}

$userId = $_SESSION['user_id'];
$userBookings = getUserBookings($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background-image: url(back.jpg);
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
        }

        nav {
            background: white;
            padding: 0 2rem;
            height: 80px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .cont img {
            height: 60px;
        }

        nav ul {
            display: flex;
            gap: 1.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav li a {
            color: black;
            font-size: 16px;
            padding: 7px 13px;
            border-radius: 4px;
            text-transform: uppercase;
            text-decoration: none;
            border-bottom: 4px solid coral;
            transition: all 0.3s ease;
        }

        .active:hover {
            background: coral;
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        main {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        h2 {
            color: white;
            font-size: 2rem;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .booking-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 3rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .booking-table th {
            background-color: #3200a0;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .booking-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .booking-item:hover {
            background-color: #f8f9fa;
        }

        .service-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .no-bookings {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            color: #666;
            font-size: 1.1rem;
        }

        .cancel-booking-btn {
            background-color: #ff4444;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .cancel-booking-btn:hover {
            background-color: #cc0000;
            transform: translateY(-1px);
        }

        .logout {
            text-decoration: none;
            color: white;
            background-color: #3200a0;
            padding: 0.75rem 1.5rem;
            border-radius: 14px;
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logout:hover {
            background-color: rgb(243, 134, 95);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .aboutus {
            background-color: rgba(255, 228, 196, 0.95);
            padding: 3rem 2rem;
            margin-top: 2rem;
        }

        .why h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        .para {
            color: #555;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        footer {
            background-color: #111;
            padding: 3rem 2rem;
            margin-top: 2rem;
        }

        .footerContainer {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .rent h1 {
            color: darkorange;
            margin-bottom: 1.5rem;
        }

        .socialIcons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .socialIcons a {
            background-color: white;
            padding: 1rem;
            border-radius: 50%;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .socialIcons a:hover {
            transform: translateY(-3px);
        }

        .socialIcons a i {
            font-size: 1.5rem;
            color: black;
            opacity: 0.9;
        }

        .phno, .addr {
            color: white;
            margin: 0.5rem 0;
            font-size: 1.1rem;
        }

        @media (max-width: 1024px) {
            .booking-table {
                font-size: 0.9rem;
            }

            .service-image {
                width: 60px;
                height: 60px;
            }
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                height: auto;
                padding: 1rem;
            }

            nav ul {
                flex-direction: column;
                width: 100%;
                text-align: center;
                gap: 0.5rem;
            }

            .booking-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .logout {
                position: static;
                display: block;
                margin: 1rem auto;
                text-align: center;
            }

            .aboutus {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="cont">
            <img src="logo.png" alt="SalonSpear Logo">
        </div>
        <ul>
            <li><a href="home.php" class="active">Home</a></li>
            <li><a href="my_booking.php" class="active">My appointments</a></li>
            <!-- <li><a href="cancel_service.php" class="active">Cancel appointments</a></li> -->
            <li><a class="active" id="contact-btn">Contact Us</a></li>
        </ul>
    </nav>
    <div class="home">
        <button><a class="logout" href="logout.php"> LOGOUT </a></button>
    </div>

    <main>
        <section>
            <h2>My Bookings</h2>
            <?php
            if (!empty($userBookings)) {
                echo '<table class="booking-table">';
                echo '<tr>';
                echo '<th>Image</th>';
                echo '<th>Service Name</th>';
                echo '<th>Description</th>';
                echo '<th>Booking Date</th>';
                echo '<th>Booking Time</th>';
                echo '<th>Customer Name</th>';
                echo '<th>Customer Phone</th>';
                echo '<th>Notes</th>';
                echo '<th>Action</th>';
                echo '</tr>';
                foreach ($userBookings as $booking) {
                    echo '<tr class="booking-item">';
                    echo '<td><img src="' . $booking['image_path'] . '" alt="' . $booking['service_name'] . '" class="service-image"></td>';
                    echo '<td>' . $booking['service_name'] . '</td>';
                    echo '<td>' . $booking['description'] . '</td>';
                    echo '<td>' . $booking['booking_date'] . '</td>';
                    echo '<td>' . $booking['booking_time'] . '</td>';
                    echo '<td>' . $booking['customer_name'] . '</td>';
                    echo '<td>' . $booking['customer_phone'] . '</td>';
                    echo '<td>' . $booking['notes'] . '</td>';
                    echo '<td><form method="POST"><button type="submit" name="cancel_booking" class="cancel-booking-btn">Cancel</button><input type="hidden" name="booking_id" value="' . $booking['id'] . '"></form></td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="no-bookings">You have no bookings.</p>';
            }
            ?>
        </section>
    </main>

   

   

    <script>
        const contact = document.querySelector("#contact-btn");
        const scrollDownToFooter = () => {
            const footer = document.querySelector("footer");
            footer.scrollIntoView({ behavior: "smooth" });
        }
        contact.addEventListener('click', scrollDownToFooter);
    </script>
</body>
</html>
