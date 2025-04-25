<?php
session_start();
include('../db_connection.php');

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

if (!$user_id) {
    header('Location: login.php');
    exit();
}

// Fetch bookings
$bookings = [];
$query = "
    SELECT b.subservice_id, b.staff_name, b.customer_name, b.address, b.phone, b.landmark, b.email, b.note, b.booking_date, b.booking_time, ss.subservice_name, s.category AS parent_category
    FROM bookings b
    JOIN subservices ss ON b.subservice_id = ss.id
    JOIN services s ON ss.parent_service_id = s.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC, b.booking_time DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = $row;
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Easy Living</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: "Inter", sans-serif;
        }
        .navbar {
            background: #ff6600;
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .nav-link {
            position: relative;
            color: white;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #ffe6cc;
        }
        .nav-link::after {
            content: "";
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: #ffe6cc;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="navbar flex justify-between items-center h-16 text-white">
        <div class="flex space-x-8">
            <a href="index.php" class="nav-link text-lg">Home</a>
            <a href="about.html" class="nav-link text-lg">About</a>
            <a href="services.php" class="nav-link text-lg">Services</a>
            <a href="favorites.php" class="nav-link text-lg">Favorites</a>
            <a href="my_bookings.php" class="nav-link text-lg">My Bookings</a>
        </div>
    </nav>

    <!-- Bookings Section -->
    <h2 class="text-4xl font-bold my-10 text-orange-600 text-center">My Bookings</h2>
    <div class="flex flex-wrap justify-center gap-8 px-4">
        <?php if (empty($bookings)): ?>
            <p class="text-lg text-gray-600">No bookings found.</p>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="w-72 bg-white/90 p-5 rounded-xl shadow-lg border border-transparent">
                    <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($booking['subservice_name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Service:</strong> <?php echo htmlspecialchars($booking['parent_category']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Staff:</strong> <?php echo htmlspecialchars($booking['staff_name']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Customer:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Address:</strong> <?php echo htmlspecialchars($booking['address']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?>
                    </p>
                    <?php if ($booking['landmark']): ?>
                        <p class="text-sm text-gray-600 mt-1">
                            <strong>Landmark:</strong> <?php echo htmlspecialchars($booking['landmark']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($booking['email']): ?>
                        <p class="text-sm text-gray-600 mt-1">
                            <strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($booking['note']): ?>
                        <p class="text-sm text-gray-600 mt-1">
                            <strong>Note:</strong> <?php echo htmlspecialchars($booking['note']); ?>
                        </p>
                    <?php endif; ?>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Time:</strong> <?php echo htmlspecialchars($booking['booking_time']); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>