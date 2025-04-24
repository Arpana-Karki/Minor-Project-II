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

// Function to get a specific service's details
function getServiceDetails($serviceId) {
    global $conn;
    $serviceDetails = null;

    $sql = "SELECT * FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $serviceDetails = $result->fetch_assoc();
    }

    $stmt->close();
    return $serviceDetails;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the service ID from the URL
$serviceId = isset($_GET['id']) ? $_GET['id'] : 0;
$serviceDetails = getServiceDetails($serviceId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Details</title>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
}

main {
    background-image: url(back.jpg);
    background-repeat: no-repeat;
    background-size: cover;
    min-height: 100vh;
    padding: 2rem;
    position: relative;
}

/* Modified navigation styles with white theme */
nav {
    background-color: #ffffff;
    height: 80px;
    width: 100%;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ccc;
}

nav img.logo {
    height: 50px;
}

nav ul {
    display: flex;
    align-items: center;
    gap: 1rem;
    list-style: none;
}

nav li a {
    color: #333;
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
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.logout {
    text-decoration: none;
    color: #fff;
    background-color: #3200a0;
    padding: 10px 20px;
    border-radius: 14px;
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    transition: all 0.3s ease;
}

.logout:hover {
    background-color: rgb(243, 134, 95);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.service-details {
    background: rgba(255, 255, 255, 0.95);
    padding: 2rem;
    border-radius: 8px;
    max-width: 800px;
    margin: 2rem auto;
}

.service-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.service-name {
    font-size: 24px;
    color: #333;
    margin-bottom: 1rem;
}

.service-description {
    color: #666;
    margin-bottom: 1rem;
}

.service-price {
    font-size: 20px;
    color: #3200a0;
    margin-bottom: 1rem;
}

.proceed-to-book-btn {
    background-color: #3200a0;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.proceed-to-book-btn:hover {
    background-color: #5434c8;
}

#booking-form {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    max-width: 600px;
    margin: 2rem auto;
}

#booking-form form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

#booking-form input,
#booking-form textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
}

#booking-form input[type="submit"] {
    background-color: #3200a0;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#booking-form input[type="submit"]:hover {
    background-color: #5434c8;
}

footer {
    background-color: #111;
    padding: 2rem;
    color: white;
}

.footerContainer {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}

.rent h1 {
    color: darkorange;
    margin-bottom: 1rem;
}

.socialIcons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin: 1rem 0;
}

.socialIcons a {
    background-color: white;
    padding: 10px;
    border-radius: 50%;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.socialIcons a:hover {
    transform: translateY(-3px);
}

.socialIcons a i {
    font-size: 2em;
    color: black;
    opacity: 0.9;
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
        margin-top: 1rem;
    }

    nav li {
        width: 100%;
    }

    nav li a {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
    }

    .logout {
        position: static;
        display: block;
        text-align: center;
        margin: 1rem auto;
    }
}
    </style>
</head>
<body>
    <nav>
        <img src="logo.png" alt="SalonSpear Logo" class="logo">
        <ul>
            <li><a href="home.php" class="active">Home</a></li>
            <li><a href="my_booking.php" class="active">My Booking</a></li>
            <li><a href="cancel_booking.php" class="active">Cancel Booking</a></li>
            <li><a class="active">Contact Us</a></li>
        </ul>
    </nav>

    <div class="template"></div>

    <div class="home">
        <button><a class="logout" href="logout.php"> LOGOUT </a></button>
    </div>

    <main>
    <section>
        <?php
        if ($serviceDetails) {
            echo '<div class="service-details">';
            echo '<img src="' . $serviceDetails['image_path'] . '" alt="' . $serviceDetails['name'] . '" class="service-image">';
            echo '<h3 class="service-name">' . $serviceDetails['name'] . '</h3>';
            echo '<p class="service-description">' . $serviceDetails['description'] . '</p>';
            echo '<p class="service-price">Price: Rs' . number_format($serviceDetails['price'], 2) . '</p>';
            echo '<button class="proceed-to-book-btn" onclick="document.getElementById(\'booking-form\').style.display=\'block\'">Proceed to Book</button>';
            echo '</div>';

            echo '<div id="booking-form" style="display:none;">';
            echo '<form action="book_service.php" method="post" onsubmit="return confirmBooking()">';
            echo '<input type="hidden" name="service_id" value="' . $serviceDetails['id'] . '">';
            echo '<label for="name">Name:</label>';
            echo '<input type="text" id="name" name="name" required>';
            echo '<label for="phone">Phone Number:</label>';
            echo '<input type="text" id="phone" name="phone" required>';
            echo '<label for="address">Service Address:</label>';
            echo '<input type="text" id="address" name="address" required>';
            echo '<label for="service_date">Service Date:</label>';
            echo '<input type="date" id="service_date" name="service_date" required>';
            echo '<label for="service_time">Service Time:</label>';
            echo '<input type="time" id="service_time" name="service_time" required>';
            echo '<label for="notes">Additional Notes:</label>';
            echo '<textarea id="notes" name="notes"></textarea>';
            echo '<input type="submit" value="Book Service">';
            echo '</form>';
            echo '</div>';
        } else {
            echo '<p class="no-service-details">No details found for this service.</p>';
        }
        ?>
    </section>
</main>

    <footer>
        <div class="footerContainer">
            <div class="rent"><h1>SalonSpear</h1></div>
            <div class="socialIcons">
                <a href=""><i class="fa-brands fa-facebook"></i></a>
                <a href=""><i class="fa-brands fa-twitter"></i></a>
                <a href=""><i class="fa-brands fa-instagram"></i></a>
            </div>
            <div>
                <p class="phno">9846464646</p>
                <p class="addr">Salon Location</p>
            </div>
        </div>
    </footer>

    <script>
    function confirmBooking() {
        return confirm('Are you sure you want to book this service?');
    }
    </script>
</body>
</html>
