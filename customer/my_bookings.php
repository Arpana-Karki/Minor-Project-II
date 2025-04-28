<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Include database connection
try {
    include('../db_connection.php');
} catch (Exception $e) {
    die("Failed to include db_connection.php: " . $e->getMessage());
}

// Check if customer is logged in (temporary bypass for testing)
$customer_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 1;

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = (int)$_POST['cancel_booking'];
    try {
        $delete_query = "DELETE FROM bookings WHERE id = ? AND customer_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'ii', $booking_id, $customer_id);
        mysqli_stmt_execute($delete_stmt);
        $affected_rows = mysqli_stmt_affected_rows($delete_stmt);
        mysqli_stmt_close($delete_stmt);
        if ($affected_rows > 0) {
            $message = "Booking cancelled successfully!";
        } else {
            $error = "No booking found with the provided ID or you do not have permission to cancel it.";
            error_log("Cancellation failed: No booking found for ID $booking_id and customer_id $customer_id");
        }
    } catch (Exception $e) {
        $error = "Failed to cancel booking: " . $e->getMessage();
        error_log("Cancellation error: " . $e->getMessage());
    }
}

// Fetch customer's bookings
try {
    $query = "
        SELECT b.id, b.subservice_name, s.name AS staff_name, b.customer_name, b.address, b.phone, b.landmark, b.note, b.booking_date, b.booking_time
        FROM bookings b
        JOIN staff s ON b.staff_id = s.id
        WHERE b.customer_id = ?
        ORDER BY b.booking_date DESC, b.booking_time DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    die("Bookings query failed: " . $e->getMessage());
}

// Get messages if set
$message = isset($message) ? $message : '';
$error = isset($error) ? $error : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Easy Living</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: "Inter", sans-serif;
        }
        .navbar {
            background: #ff6600;
            padding: 0.75rem 1.5rem;
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
        .table-container {
            max-width: 90%;
            margin: 2rem auto;
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #ff6600;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f3f4f6;
        }
        .no-bookings {
            text-align: center;
            color: #6b7280;
            font-size: 1.125rem;
            margin-top: 2rem;
        }
        .cancel-btn {
            background: #6b7280;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 0.875rem;
            border: none;
        }
        .cancel-btn:hover {
            background: #4b5563;
        }
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="navbar flex justify-between items-center h-12 text-white">
        <div class="flex space-x-6">
            <a href="../index.php" class="nav-link text-base">Home</a>
            <a href="services.php" class="nav-link text-base">Services</a>
            <a href="../about.php" class="nav-link text-base">About</a>
            <a href="favorites.php" class="nav-link text-base">Favorites</a>
            <a href="../profile.php" class="nav-link text-base">My Profile</a>
            <a href="my_bookings.php" class="nav-link text-base">My Bookings</a>
            <a href="../customer_logout.php" class="nav-link text-base">Logout</a>
        </div>
    </nav>

    <!-- Bookings Section -->
    <div class="table-container">
        <h2 class="text-2xl font-bold text-orange-600 mb-4 text-center">My Bookings</h2>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($bookings)): ?>
            <p class="no-bookings">You have no bookings yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Subservice</th>
                        <th>Staff</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Landmark</th>
                        <th>Note</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['subservice_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['staff_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['address']); ?></td>
                            <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                            <td><?php echo htmlspecialchars($booking['landmark'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($booking['note'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                            <td>
                                <form method="POST" action="my_bookings.php" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="cancel_booking" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" class="cancel-btn">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        // Display messages
        <?php if ($message): ?>
            alert("<?php echo addslashes($message); ?>");
        <?php endif; ?>
    </script>
</body>
</html>