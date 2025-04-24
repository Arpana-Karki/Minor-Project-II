<?php

// connection.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon_db"; // Update database name to salon_db

// Create connection
$con = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start(); 

// Function to get all bookings
function getAllBookings($con) {
    $bookings = array();
    $sql = "SELECT b.id, b.service_id, u.full_name, b.phone, b.notes, s.name AS service_name, s.description, s.image_path, b.booking_time
            FROM bookings b
            INNER JOIN services s ON b.service_id = s.id
            INNER JOIN registered_users u ON b.user_id = u.id";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $bookings[] = $row;
        }
    }
    return $bookings;
}

// Check if the user is logged in and is an admin by email
if (!isset($_SESSION['logged_in']) || $_SESSION['email'] != 'salon@gmail.com') { // Change to your admin email
    // Redirect to login page if not admin
    header("Location: login.php");
    exit();
}

// Fetch all bookings
$allBookings = getAllBookings($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
        }
        body {
            background-image: url(back.jpg);
            background-repeat: no-repeat;
            background-size: cover;
            width: 100%;
            height: 100%;
        }
        h1 {
            color: #fff;
        }
        .home {
            font-weight: 900 !important;
        }
        main {
            margin-bottom: 200px;
        }
        .logout {
            text-decoration: none !important;
            color: #fff !important;
            background-color: #3200a0 !important;
            padding: 10px;
            border: 2px solid transparent;
            border-radius: 14px;
            top: 90%;
            right: 20px;
            position: fixed;
        }
        .logout:hover {
            background-color: rgb(243, 134, 95) !important;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
        }
        button {
            border: none;
            float: right;
            background: none;
        }
        nav {
            background: linear-gradient(to right,hsl(0, 0.00%, 100.00%),rgb(199, 202, 178),rgb(227, 234, 92));
            height: 80px;
            width: 100%;
        }
        .cont {
            color: white;
            font-size: 35px;
            line-height: 80px;
            padding: 0px;
            font-weight: bold;
            margin-left: 20px;
        }
        .cont:hover {
            color: coral;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
            text-shadow: 0 0 3px #FF0000;
        }
        ul {
            float: right;
            margin-right: 100px;
            margin-top: -10px;
        }
        li {
            display: inline-block;
            line-height: 80px;
            margin: 0 5px;
        }
        li a {
            color: white;
            font-size: 16px;
            padding: 7px 13px;
            border-radius: 4px;
            text-transform: uppercase;
            border-bottom: 4px solid coral;
        }
        .active {
            text-decoration: none;
            background: transparent;
            transition: 0.5s;
        }
        .active:hover {
            background: coral;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #2196f3;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .service-image {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <nav>
    <div style="position: absolute; top: 0; left: 0; padding: 10px;">
  <img src="logo.png" alt="Logo" style="height: 50px;">
</div>

        <ul>
            <li><a href="ad_admin.php" class="active">Home</a></li>
            <li><a href="view_booking.php" class="active">View bookings</a></li>
            <li><a href="add_service.php" class="active">Add Service</a></li>
            <li><a href="remove_service.php" class="active">Remove Service</a></li>
            <li><a class="active" id="contact-btn">Contact Us</a></li>
        </ul> 
    </nav>
    <div class="home">
        <button><a class="logout" href="logout.php"> LOGOUT </a></button>
    </div>

    <main>
    <section>
        <h2>All Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Name</th>
                    <th>User Name</th>
                    <th>Phone</th>
                    <th>Notes</th>
                    <th>Booking Time</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($allBookings)) {
                    // HTML/PHP section to display the bookings
                    foreach ($allBookings as $booking) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($booking['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['service_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['full_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['phone']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['notes']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['booking_time']) . '</td>';
                        echo '<td><img src="' . htmlspecialchars($booking['image_path']) . '" class="service-image"></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">No bookings have been made.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </section>
    </main>

    <footer>
        <div class="footerContainer">
            <div class="rent"><h1>SalonSpear</h1></div>
        </div>
    </footer>
</body>
</html>
