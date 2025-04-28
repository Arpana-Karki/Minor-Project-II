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

// // Check if customer is logged in
// $customer_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;
// if ($customer_id === 0) {
//     $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
//     header("Location: ../customer_login.php");
//     exit();
// }

// Initialize session favorites
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

// Handle remove from favorites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $subservice_id = (int)$_POST['remove_favorite'];
    try {
        $delete_query = "DELETE FROM favorites WHERE customer_id = ? AND subservice_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'ii', $customer_id, $subservice_id);
        mysqli_stmt_execute($delete_stmt);
        mysqli_stmt_close($delete_stmt);
        $message = "Removed from Favorites!";
    } catch (Exception $e) {
        $message = "Error removing from favorites: " . $e->getMessage();
        error_log("Favorites error: " . $e->getMessage());
    }
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $subservice_id = (int)$_POST['subservice_id'];
    $staff_id = (int)$_POST['staff_id'];
    $subservice_name = mysqli_real_escape_string($conn, $_POST['subservice_name']);
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $landmark = !empty($_POST['landmark']) ? mysqli_real_escape_string($conn, $_POST['landmark']) : null;
    $note = !empty($_POST['note']) ? mysqli_real_escape_string($conn, $_POST['note']) : null;
    $booking_datetime = $_POST['booking_datetime'];

    // Parse datetime-local input
    $datetime = DateTime::createFromFormat('Y-m-d\TH:i', $booking_datetime);
    if ($datetime) {
        $booking_date = $datetime->format('Y-m-d');
        $booking_time = $datetime->format('H:i:s');
    } else {
        $booking_date = null;
        $booking_time = null;
    }

    // Log form data for debugging
    error_log("Booking data: " . print_r([
        'subservice_id' => $subservice_id,
        'staff_id' => $staff_id,
        'subservice_name' => $subservice_name,
        'customer_name' => $customer_name,
        'address' => $address,
        'phone' => $phone,
        'landmark' => $landmark,
        'note' => $note,
        'booking_date' => $booking_date,
        'booking_time' => $booking_time,
        'customer_id' => $customer_id
    ], true));

    // Validate required fields
    $missing_fields = [];
    if (empty($subservice_id)) $missing_fields[] = 'subservice_id';
    if (empty($staff_id)) $missing_fields[] = 'staff_id';
    if (empty($subservice_name)) $missing_fields[] = 'subservice_name';
    if (empty($customer_name)) $missing_fields[] = 'customer_name';
    if (empty($address)) $missing_fields[] = 'address';
    if (empty($phone)) $missing_fields[] = 'phone';
    if (empty($booking_date)) $missing_fields[] = 'booking_date';
    if (empty($booking_time)) $missing_fields[] = 'booking_time';

    if (!empty($missing_fields)) {
        $booking_error = "The following required fields are missing or invalid: " . implode(', ', $missing_fields);
        error_log("Validation failed: " . $booking_error);
    } else {
        try {
            $query = "INSERT INTO bookings (subservice_id, subservice_name, staff_id, customer_name, address, phone, landmark, note, booking_date, booking_time, customer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'isisssssssi', $subservice_id, $subservice_name, $staff_id, $customer_name, $address, $phone, $landmark, $note, $booking_date, $booking_time, $customer_id);
            mysqli_stmt_execute($stmt);
            $booking_message = "Your booking is confirmed!";
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            $booking_error = "Failed to save booking: " . $e->getMessage();
            error_log("Booking error: " . $e->getMessage());
        }
    }
}

// Get messages if set
$message = isset($message) ? $message : '';
$booking_message = isset($booking_message) ? $booking_message : '';
$booking_error = isset($booking_error) ? $booking_error : '';

// Fetch favorites
$favorites = [];
try {
    $query = "
        SELECT ss.id, ss.subservice_name, ss.amount, ss.photo, s.category AS parent_category,
               GROUP_CONCAT(st.name SEPARATOR ', ') AS staff_names,
               GROUP_CONCAT(st.id) AS staff_ids
        FROM favorites f
        JOIN subservices ss ON f.subservice_id = ss.id
        JOIN services s ON ss.parent_service_id = s.id
        LEFT JOIN staff_subservices sts ON ss.id = sts.subservice_id
        LEFT JOIN staff st ON sts.staff_id = st.id
        WHERE f.customer_id = ?
        GROUP BY ss.id
        ORDER BY ss.subservice_name";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['first_staff_name'] = $row['staff_names'] ? explode(', ', $row['staff_names'])[0] : 'Not assigned';
        $row['first_staff_id'] = $row['staff_ids'] ? explode(',', $row['staff_ids'])[0] : 0;
        $favorites[] = $row;
    }
    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    die("Favorites query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - Easy Living</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: "Inter", sans-serif;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
            font-size: 1rem;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #4f46e5;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        .action-btn {
            background: linear-gradient(to right, #4f46e5, #7c3aed);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        .action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .animate-scale-in {
            animation: scaleIn 0.5s ease-out forwards;
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .menu-toggle {
            display: none;
            cursor: pointer;
        }

        /* Favorites Section */
        .glow-button {
            position: relative;
            overflow: hidden;
        }
        .glow-button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }
        .glow-button:hover::before {
            left: 100%;
        }
        .favorites-button {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            color: white;
            transition: background-color 0.3s;
        }
        .favorites-button.remove {
            background: #6b7280;
        }
        .favorites-button.remove:hover {
            background: #4b5563;
        }
        .booking-container {
            visibility: hidden;
            opacity: 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .booking-form {
            background: #fff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            width: 100%;
            max-width: 32rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .booking-form input,
        .booking-form textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            box-sizing: border-box;
        }
        .booking-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #333;
            font-size: 0.875rem;
        }
        .booking-form button {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 0.875rem;
        }
        .booking-form .confirm-btn {
            background: #4f46e5;
            color: white;
            border: none;
        }
        .booking-form .confirm-btn:hover {
            background: #4338ca;
        }
        .booking-form .close-btn {
            background: #6b7280;
            color: white;
            border: none;
        }
        .booking-form .close-btn:hover {
            background: #4b5563;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        /* Responsive Design for Navbar */
        @media (max-width: 768px) {
            .navbar ul {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #f8f9fa;
                padding: 1rem;
            }

            .navbar ul.active {
                display: flex;
            }

            .menu-toggle {
                display: block;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="navbar sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="../index.php" class="text-2xl font-bold text-indigo-600 animate-scale-in">EasyLiving</a>
            <div class="flex items-center space-x-6">
                <ul class="flex space-x-6 text-gray-700">
                    <li><a href="../index.php" class="nav-link hover:text-indigo-600">Home</a></li>
                    <li><a href="../about.php" class="nav-link hover:text-indigo-600">About</a></li>
                    <li><a href="./favorites.php" class="nav-link hover:text-indigo-600">Packages</a></li>
                    <li><a href="./subservice.php" class="nav-link hover:text-indigo-600">Services</a></li>
                </ul>
                <!-- Wishlist -->
                <a href="favorites.php" class="action-btn">
                    <i class="fas fa-heart mr-2"></i> Favorites
                </a>
                <!-- My Bookings -->
                <a href="my_bookings.php" class="action-btn">
                    <i class="fas fa-calendar-check mr-2"></i> My Bookings
                </a>
                <!-- My Profile -->
                <a href="../profile.php" class="action-btn">
                    <i class="fas fa-user-circle mr-2"></i> My Profile
                </a>
                <div class="menu-toggle text-gray-700"><i class="fas fa-bars"></i></div>
            </div>
        </div>
    </nav>

    <!-- Favorites Section -->
    <h2 class="text-3xl font-bold my-8 text-indigo-600 text-center">My Favorites</h2>
    <div class="flex flex-wrap justify-center gap-6 px-4">
        <?php if (empty($favorites)): ?>
            <p class="text-base text-gray-600">No favorites added yet.</p>
        <?php else: ?>
            <?php foreach ($favorites as $favorite): ?>
                <div class="w-64 bg-white/90 p-4 rounded-lg shadow-md border border-transparent relative">
                    <img
                        src="../Uploads/services/<?php echo htmlspecialchars($favorite['photo'] ?: 'default.jpg'); ?>"
                        alt="<?php echo htmlspecialchars($favorite['subservice_name']); ?>"
                        class="w-full h-48 object-cover rounded-lg"
                    />
                    <form method="POST" action="favorites.php" class="inline">
                        <input type="hidden" name="remove_favorite" value="<?php echo $favorite['id']; ?>">
                        <button type="submit" class="favorites-button remove">
                            <i class="fas fa-heart"></i> Remove
                        </button>
                    </form>
                    <h3 class="mt-3 text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($favorite['subservice_name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Service:</strong> <?php echo htmlspecialchars($favorite['parent_category']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Staff:</strong>
                        <?php
                        if ($favorite['staff_names']) {
                            $names = explode(', ', $favorite['staff_names']);
                            $ids = explode(',', $favorite['staff_ids']);
                            foreach ($names as $index => $name) {
                                $staff_id = $ids[$index];
                                echo '<a href="../admin/staff_profile.php?id=' . htmlspecialchars($staff_id) . '" class="text-indigo-600 hover:underline">' . htmlspecialchars($name) . '</a>';
                                if ($index < count($names) - 1) echo ', ';
                            }
                        } else {
                            echo 'Not assigned';
                        }
                        ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Price:</strong> Rs <?php echo number_format($favorite['amount'], 2); ?>
                    </p>
                    <button
                        class="glow-button mt-3 text-base font-semibold bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 w-full"
                        onclick="openBookingForm(<?php echo $favorite['id']; ?>, '<?php echo htmlspecialchars(addslashes($favorite['subservice_name'])); ?>', '<?php echo htmlspecialchars(addslashes($favorite['first_staff_name'])); ?>', <?php echo $favorite['first_staff_id']; ?>)"
                    >
                        Book Now
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Booking Form -->
    <div class="booking-container" id="bookingContainer">
        <div class="booking-form" id="bookingForm">
            <?php if ($booking_error): ?>
                <div class="error-message"><?php echo htmlspecialchars($booking_error); ?></div>
            <?php endif; ?>
            <h2 class="text-xl font-bold text-indigo-600 mb-3">Book Service</h2>
            <form method="POST" action="favorites.php">
                <input type="hidden" name="subservice_id" id="subserviceId">
                <input type="hidden" name="staff_id" id="staffId">
                <div>
                    <label for="serviceName">Service</label>
                    <input type="text" id="serviceName" name="subservice_name" readonly />
                </div>
                <div>
                    <label for="staffName">Staff</label>
                    <input type="text" id="staffName" name="staff_name" readonly />
                </div>
                <div>
                    <label for="customerName">Name</label>
                    <input type="text" id="customerName" name="customer_name" placeholder="Your Name" required />
                </div>
                <div>
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" placeholder="Address" required />
                </div>
                <div>
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" placeholder="Phone Number" pattern="[0-9]{10}" required />
                </div>
                <div>
                    <label for="landmark">Landmark (Optional)</label>
                    <input type="text" id="landmark" name="landmark" placeholder="Landmark" />
                </div>
                <div>
                    <label for="note">Note (Optional)</label>
                    <textarea id="note" name="note" placeholder="Any special instructions" rows="2"></textarea>
                </div>
                <div>
                    <label for="bookingDatetime">Date & Time</label>
                    <input type="datetime-local" id="bookingDatetime" name="booking_datetime" min="2025-04-26T06:00" max="2025-12-31T21:00" required />
                </div>
                <div class="button-group">
                    <button type="submit" name="confirm_booking" class="confirm-btn">Book</button>
                    <button type="button" class="close-btn" onclick="closeBookingForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Display messages
        <?php if ($message): ?>
            alert("<?php echo addslashes($message); ?>");
        <?php endif; ?>
        <?php if ($booking_message): ?>
            alert("<?php echo addslashes($booking_message); ?>");
            window.location.href = '/easy/customer/my_bookings.php';
        <?php endif; ?>

        // Open Booking Form
        function openBookingForm(subserviceId, subserviceName, staffName, staffId) {
            const bookingContainer = document.getElementById("bookingContainer");
            document.getElementById("subserviceId").value = subserviceId;
            document.getElementById("serviceName").value = subserviceName;
            document.getElementById("staffName").value = staffName;
            document.getElementById("staffId").value = staffId;
            if (staffId == 0) {
                alert("No staff assigned to this subservice.");
                return;
            }
            bookingContainer.style.visibility = "visible";
            bookingContainer.style.opacity = "1";
        }

        // Close Booking Form
        function closeBookingForm() {
            const bookingContainer = document.getElementById("bookingContainer");
            bookingContainer.style.visibility = "hidden";
            bookingContainer.style.opacity = "0";
            document.getElementById("bookingForm").querySelector('form').reset();
        }

        // Validate Datetime Input
        document.getElementById('bookingDatetime').addEventListener('change', function (e) {
            const datetime = new Date(e.target.value);
            const hours = datetime.getHours();
            const minutes = datetime.getMinutes();
            if (hours < 6 || hours > 21 || (hours === 21 && minutes > 0)) {
                alert('Please select a time between 6:00 AM and 9:00 PM.');
                e.target.value = '';
            }
        });

        // Validate Form Submission
        document.querySelector('#bookingForm form').addEventListener('submit', function (e) {
            const staffId = document.getElementById('staffId').value;
            const datetime = document.getElementById('bookingDatetime').value;
            if (!staffId) {
                e.preventDefault();
                alert('No staff assigned to this subservice.');
            }
            if (!datetime) {
                e.preventDefault();
                alert('Please select a valid date and time between 6:00 AM and 9:00 PM.');
            }
        });

        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.navbar ul');
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }
    </script>
</body>
</html>