<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// TEMPORARY: Session check commented out for testing
// if (!isset($_SESSION['customer_id']) || empty($_SESSION['customer_id'])) {
//     header("Location: /easy/customer/login.php");
//     exit();
// }

// Database connection
include('../db_connection.php');

// Check if connection is established
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed. Please try again later.");
}

// Handle Booking Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_package'])) {
    error_log("Form submitted with data: " . print_r($_POST, true));

    // TEMPORARY: Use fallback customer_id
    $customer_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 1;
    error_log("Using customer_id: " . $customer_id);

    // Sanitize and validate inputs
    $package_id = filter_input(INPUT_POST, 'package_id', FILTER_SANITIZE_NUMBER_INT);
    $package_name = trim(strip_tags($_POST['package_name'] ?? ''));
    $customer_name = trim(strip_tags($_POST['customer_name'] ?? ''));
    $address = trim(strip_tags($_POST['address'] ?? ''));
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    $landmark = trim(strip_tags($_POST['landmark'] ?? ''));
    $note = trim(strip_tags($_POST['note'] ?? ''));
    $booking_date = trim(strip_tags($_POST['booking_date'] ?? ''));
    $booking_time = trim(strip_tags($_POST['booking_time'] ?? ''));
    $is_package = 1;

    // Validation
    if (empty($package_id) || $package_id <= 0) {
        die("Error: Invalid package ID.");
    }
    if (empty($package_name)) {
        die("Error: Package name is required.");
    }
    if (empty($customer_name) || strlen($customer_name) > 100) {
        die("Error: Customer name is required and must be 100 characters or less.");
    }
    if (empty($address)) {
        die("Error: Address is required.");
    }
    if (empty($phone) || !preg_match("/^[0-9]{10}$/", $phone)) {
        die("Error: Valid 10-digit phone number is required.");
    }
    if (!empty($landmark) && strlen($landmark) > 255) {
        die("Error: Landmark must be 255 characters or less.");
    }
    if (!empty($note) && strlen($note) > 1000) {
        die("Error: Note must be 1000 characters or less.");
    }
    if (empty($booking_date) || !strtotime($booking_date)) {
        die("Error: Valid booking date is required.");
    }
    if (empty($booking_time) || !strtotime($booking_time)) {
        die("Error: Valid booking time is required.");
    }

    // Fetch services associated with the package
    $sql = "SELECT ps.service_id, s.service_name 
            FROM package_services ps 
            JOIN services s ON ps.service_id = s.id 
            WHERE ps.package_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($services)) {
        die("Error: No services found for this package.");
    }

    // Fetch available staff for services
    $staff_sql = "SELECT id FROM staff WHERE service_category IN (SELECT service_name FROM services WHERE id IN (SELECT service_id FROM package_services WHERE package_id = ?)) LIMIT 1";
    $staff_stmt = $conn->prepare($staff_sql);
    $staff_stmt->bind_param("i", $package_id);
    $staff_stmt->execute();
    $staff_result = $staff_stmt->get_result()->fetch_assoc();
    $staff_id = $staff_result['id'] ?? null;
    $staff_stmt->close();

    if (!$staff_id) {
        die("Error: No staff available for this package.");
    }

    // Insert a booking for each service in the package
    $sql = "INSERT INTO bookings 
        (subservice_id, subservice_name, staff_id, customer_name, address, phone, landmark, note, booking_date, booking_time, customer_id, is_package) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        foreach ($services as $service) {
            $stmt->bind_param(
                "isssssssssii",
                $service['service_id'],
                $service['service_name'],
                $staff_id,
                $customer_name,
                $address,
                $phone,
                $landmark,
                $note,
                $booking_date,
                $booking_time,
                $customer_id,
                $is_package
            );
            if (!$stmt->execute()) {
                die("Error: Failed to save booking for service {$service['service_name']}. " . $stmt->error);
            }
        }
        $_SESSION['booking_message'] = "Package booking successful!";
        $stmt->close();
    } else {
        die("Error: Database prepare error: " . $conn->error);
    }

    header("Location: /easy/customer/package.php");
    exit();
}

// Display booking message if set
$booking_message = isset($_SESSION['booking_message']) ? $_SESSION['booking_message'] : '';
if ($booking_message) {
    unset($_SESSION['booking_message']);
}

// Fetch Packages and their associated services
$sql = "SELECT p.id, p.package_name, p.package_description, p.package_price, p.package_image, 
        GROUP_CONCAT(s.service_name) AS service_names
        FROM packages p
        LEFT JOIN package_services ps ON p.id = ps.package_id
        LEFT JOIN services s ON ps.service_id = s.id
        GROUP BY p.id";
$result = $conn->query($sql);
if (!$result) {
    die("Error: Cannot fetch packages. " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Packages | Easy Living</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Same styles as provided */
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background: #f4f7fc;
            min-height: 100vh;
        }
        .navbar {
            background: #1a202c;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .navbar a:hover {
            color: #F28C38;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        h1 {
            text-align: center;
            color: #1a202c;
            font-weight: 600;
            margin-bottom: 40px;
        }
        .package-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .package-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .package-card:hover {
            transform: translateY(-10px);
        }
        .package-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .package-content {
            padding: 20px;
        }
        .package-content h3 {
            color: #1a202c;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .package-content p {
            color: #4a5568;
            font-size: 1rem;
            margin-bottom: 15px;
        }
        .package-content .price {
            color: #2f855a;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .package-content .staff {
            color: #4a5568;
            font-style: italic;
            margin-bottom: 15px;
        }
        .book-btn {
            display: inline-block;
            background: #F28C38;
            color: #fff;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s ease;
        }
        .book-btn:hover {
            background: #d97706;
            transform: scale(1.05);
        }
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .popup-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            position: relative;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #1a202c;
            font-size: 20px;
            cursor: pointer;
        }
        .popup-content h2 {
            color: #1a202c;
            font-size: 1.25rem;
            margin-bottom: 15px;
            text-align: center;
        }
        .popup-content label {
            color: #1a202c;
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .popup-content input,
        .popup-content textarea,
        .popup-content select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            background: #f9fafb;
            color: #1a202c;
            font-size: 0.9rem;
        }
        .popup-content input[readonly] {
            background: #e2e8f0;
            cursor: not-allowed;
        }
        .popup-content textarea {
            resize: vertical;
            min-height: 80px;
        }
        .popup-content button {
            background: #F28C38;
            color: #fff;
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }
        .popup-content button:hover {
            background: #d97706;
            transform: scale(1.05);
        }
        .success-message {
            background: #2f855a;
            color: #fff;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="navbar">
    <ul>
        <li><a href="/easy/customer/index.php">Home</a></li>
        <li><a href="/easy/customer/services.php">Services</a></li>
        <li><a href="/easy/customer/package.php">Packages</a></li>
        <li><a href="/easy/customer/profile.php">My Profile</a></li>
        <li><a href="/easy/customer/logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h1>Our Packages</h1>

    <?php if (!empty($booking_message)): ?>
        <p style="text-align:center; color:green; margin-bottom:20px;"><?= htmlspecialchars($booking_message) ?></p>
    <?php endif; ?>

    <div class="package-grid">
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="package-card">
                <?php if (!empty($row['package_image'])): ?>
                    <img src="/easy/admin/uploads/<?= htmlspecialchars($row['package_image']) ?>" alt="<?= htmlspecialchars($row['package_name']) ?>">
                <?php else: ?>
                    <img src="/easy/assets/default-package.jpg" alt="Default Package">
                <?php endif; ?>
                <div class="package-content">
                    <h3><?= htmlspecialchars($row['package_name']) ?></h3>
                    <p>Services: <?= htmlspecialchars($row['service_names'] ?: 'Not specified') ?></p>
                    <p class="price">₹ <?= number_format($row['package_price'], 2) ?></p>
                    <a href="#" class="book-btn" onclick='openPopup(<?= json_encode([
                        "id" => $row["id"],
                        "package_name" => $row["package_name"],
                        "service_names" => $row["service_names"]
                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Book Now</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Popup Form -->
<div class="popup" id="popup">
    <div class="popup-content">
        <span class="close-btn" onclick="closePopup()">×</span>
        <h2>Book Package</h2>
        <form method="post" action="">
            <input type="hidden" name="package_id" id="popup_package_id">
            <input type="hidden" name="package_name" id="popup_package_name">

            <label>Package Services</label>
            <input type="text" id="popup_service_names" readonly>

            <label>Customer Name</label>
            <input type="text" name="customer_name" required>

            <label>Address</label>
            <input type="text" name="address" required>

            <label>Phone Number</label>
            <input type="text" name="phone" required maxlength="10" pattern="[0-9]{10}">

            <label>Landmark</label>
            <input type="text" name="landmark">

            <label>Note</label>
            <textarea name="note"></textarea>

            <label>Booking Date</label>
            <input type="date" name="booking_date" required>

            <label>Booking Time</label>
            <input type="time" name="booking_time" required>

            <button type="submit" name="book_package">Confirm Booking</button>
        </form>
    </div>
</div>

<script>
function openPopup(data) {
    document.getElementById('popup_package_id').value = data.id;
    document.getElementById('popup_package_name').value = data.package_name;
    document.getElementById('popup_service_names').value = data.service_names || 'Not specified';
    document.getElementById('popup').style.display = 'flex';
}
function closePopup() {
    document.getElementById('popup').style.display = 'none';
}
</script>

</body>
</html>