<?php

// Database configuration
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'car_rental_combined_db'; // Replace with your actual database name

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get user bookings
function getUserBookings($userId) {
    global $conn;
    $bookings = array();

    $sql = "SELECT bookings.id, cars.name AS car_name, cars.image_path, bookings.booking_date
            FROM bookings
            INNER JOIN cars ON bookings.car_id = cars.id
            WHERE bookings.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    return $bookings;
}

// Other common functions can be added here

?>

