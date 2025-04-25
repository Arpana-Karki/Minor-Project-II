<?php
session_start();
include('../db_connection.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if customer is logged in
$customer_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;

// Debug: Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Initialize session favorites for non-logged-in customers
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

// Handle favorites toggle (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorites'])) {
    $subservice_id = (int)$_POST['toggle_favorites'];
    if ($customer_id) {
        $check_query = "SELECT id FROM favorites WHERE customer_id = ? AND subservice_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'ii', $customer_id, $subservice_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $delete_query = "DELETE FROM favorites WHERE customer_id = ? AND subservice_id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, 'ii', $customer_id, $subservice_id);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);
            $message = "Removed from Favorites!";
        } else {
            $insert_query = "INSERT INTO favorites (customer_id, subservice_id) VALUES (?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'ii', $customer_id, $subservice_id);
            mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);
            $message = "Added to Favorites!";
        }
        mysqli_stmt_close($check_stmt);
    } else {
        if (in_array($subservice_id, $_SESSION['favorites'])) {
            $_SESSION['favorites'] = array_diff($_SESSION['favorites'], [$subservice_id]);
            $message = "Removed from Favorites!";
        } else {
            $_SESSION['favorites'][] = $subservice_id;
            $message = "Added to Favorites!";
        }
    }
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    if ($customer_id === 0) {
        error_log("Customer not logged in, redirecting to customer_login.php");
        $booking_error = "You must be logged in to make a booking.";
        $_SESSION['redirect_after_login'] = '/easy/customer/subservice.php';
        header('Location: /easy/customer_login.php');
        exit();
    }

    $subservice_id = (int)$_POST['subservice_id'];
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $landmark = mysqli_real_escape_string($conn, $_POST['landmark']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $note = mysqli_real_escape_string($conn, $_POST['note']);
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];

    error_log("Booking data: subservice_id=$subservice_id, customer_name=$customer_name, booking_date=$booking_date, booking_time=$booking_time, customer_id=$customer_id");

    if (empty($customer_name) || empty($address) || empty($phone) || empty($booking_date) || empty($booking_time)) {
        $booking_error = "All required fields must be filled.";
    } else {
        $query = "INSERT INTO bookings (subservice_id, staff_name, customer_name, address, phone, landmark, email, note, booking_date, booking_time, customer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            $booking_error = "Prepare failed: " . mysqli_error($conn);
            error_log("SQL Prepare Error: " . mysqli_error($conn));
        } else {
            mysqli_stmt_bind_param($stmt, 'isssssssssi', $subservice_id, $staff_name, $customer_name, $address, $phone, $landmark, $email, $note, $booking_date, $booking_time, $customer_id);
            if (mysqli_stmt_execute($stmt)) {
                $booking_message = "Your booking is confirmed!";
            } else {
                $booking_error = "Failed to save booking: " . mysqli_stmt_error($stmt);
                error_log("SQL Execute Error: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get messages if set
$message = isset($message) ? $message : '';
$booking_message = isset($booking_message) ? $booking_message : '';
$booking_error = isset($booking_error) ? $booking_error : '';

// Fetch all main services for slider
$services_query = "SELECT id, category, COALESCE(photo, '') AS photo FROM services ORDER BY category";
$services_result = mysqli_query($conn, $services_query);
if (!$services_result) {
    die("Error fetching services: " . mysqli_error($conn));
}
$services = [];
while ($service = mysqli_fetch_assoc($services_result)) {
    $services[] = $service;
}

// Handle parent service filter and search query
$parent_service_id = isset($_GET['parent_service_id']) ? (int)$_GET['parent_service_id'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$subservices_query = "
    SELECT ss.id, ss.subservice_name, ss.staff_name, ss.price_per_hour, ss.photo, s.category AS parent_category
    FROM subservices ss
    JOIN services s ON ss.parent_service_id = s.id";
$conditions = [];
$params = [];
$types = '';

if ($parent_service_id) {
    $conditions[] = "ss.parent_service_id = ?";
    $params[] = $parent_service_id;
    $types .= 'i';
}

if (!empty($search)) {
    $conditions[] = "(ss.subservice_name LIKE ? OR s.category LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($conditions)) {
    $subservices_query .= " WHERE " . implode(" AND ", $conditions);
}
$subservices_query .= " ORDER BY ss.subservice_name";

$stmt = mysqli_prepare($conn, $subservices_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$subservices_result = mysqli_stmt_get_result($stmt);
$subservices = [];
while ($subservice = mysqli_fetch_assoc($subservices_result)) {
    $subservices[] = $subservice;
}
mysqli_stmt_close($stmt);

// Get customer's favorites from database (for logged-in customers)
$favorites = $_SESSION['favorites'];
if ($customer_id) {
    $favorites_query = "SELECT subservice_id FROM favorites WHERE customer_id = ?";
    $favorites_stmt = mysqli_prepare($conn, $favorites_query);
    mysqli_stmt_bind_param($favorites_stmt, 'i', $customer_id);
    mysqli_stmt_execute($favorites_stmt);
    $favorites_result = mysqli_stmt_get_result($favorites_stmt);
    $favorites = [];
    while ($row = mysqli_fetch_assoc($favorites_result)) {
        $favorites[] = $row['subservice_id'];
    }
    mysqli_stmt_close($favorites_stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Sub-Services - Easy Living</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
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
        .search-box:focus {
            box-shadow: 0 0 15px rgba(255, 102, 0, 0.6);
            border-color: #ff6600;
        }
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
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
        }
        .glow-button:hover::before {
            left: 100%;
        }
        .search-container {
            position: relative;
            width: 100%;
            max-width: 32rem;
        }
        .search-button {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
        }
        .favorites-button {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            color: white;
            transition: background-color 0.3s;
        }
        .favorites-button.add {
            background: #ff6600;
        }
        .favorites-button.add:hover {
            background: #e65c00;
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
            border-radius: 0.75rem;
            width: 100%;
            max-width: 28rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .booking-form input,
        .booking-form select,
        .booking-form textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            box-sizing: border-box;
        }
        .booking-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .booking-form button {
            padding: 0.75rem 1.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .booking-form .confirm-btn {
            background: #ff6600;
            color: white;
            border: none;
        }
        .booking-form .confirm-btn:hover {
            background: #e65c00;
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
        }
        .swiper-container {
            max-width: 100%;
            padding: 1rem 0;
        }
        .swiper-slide {
            text-align: center;
            cursor: pointer;
        }
        .swiper-slide img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        .swiper-slide h3 {
            margin-top: 0.5rem;
            font-size: 1.25rem;
            color: #333;
        }
        .swiper-button-next, .swiper-button-prev {
            color: #ff6600;
        }
        .all-services-link {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ff6600;
            transition: color 0.3s ease;
        }
        .all-services-link:hover {
            color: #e65c00;
        }
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="navbar flex justify-between items-center h-16 text-white">
        <div class="flex space-x-8">
            <a href="../index.php" class="nav-link text-lg">Home</a>
            <a href="services.php" class="nav-link text-lg">Services</a>
            <a href="../about.php" class="nav-link text-lg">About</a>
            <a href="favorites.php" class="nav-link text-lg">Favorites</a>
            <a href="../profile.php" class="nav-link text-lg">My Profile</a>
            <a href="my_bookings.php" class="nav-link text-lg">My Bookings</a>
            <a href="../customer_login.php" class="nav-link text-lg">Logout</a>
        </div>
    </nav>

    <!-- Search Bar -->
    <div class="flex justify-center items-center my-10">
        <div class="search-container">
            <input
                type="text"
                id="searchInput"
                class="search-box w-full p-4 pr-12 border-2 border-orange-500 rounded-full text-lg transition-all duration-300 shadow-lg focus:outline-none bg-white/80"
                placeholder="Search for sub-services..."
                value="<?php echo htmlspecialchars($search); ?>"
                oninput="searchServices()"
            />
            <button
                onclick="document.getElementById('searchForm').submit();"
                class="search-button p-2 bg-orange-500 text-white rounded-full text-lg cursor-pointer hover:bg-orange-600 hover:scale-110 transition-all duration-300"
            >
                🔍
            </button>
            <!-- Hidden form for server-side search -->
            <form id="searchForm" action="/easy/customer/subservice.php<?php echo $parent_service_id ? '?parent_service_id=' . $parent_service_id : ''; ?>" method="GET" style="display: none;">
                <input type="text" name="search" id="searchFormInput">
                <?php if ($parent_service_id): ?>
                    <input type="hidden" name="parent_service_id" value="<?php echo $parent_service_id; ?>">
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- All Services Link -->
    <div class="px-4 sm:px-6 lg:px-8">
        <a href="/easy/customer/subservice.php" class="all-services-link">All Services</a>
    </div>

    <!-- Service Category Slider -->
    <div class="swiper-container my-10">
        <div class="swiper-wrapper">
            <?php foreach ($services as $service): ?>
                <div class="swiper-slide" onclick="window.location.href='/easy/customer/subservice.php?parent_service_id=<?php echo $service['id']; ?>'">
                    <img src="../Uploads/services/<?php echo htmlspecialchars($service['photo'] ?: 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($service['category']); ?>">
                    <h3><?php echo htmlspecialchars($service['category']); ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>

    <!-- Sub-Services Section -->
    <h2 class="text-4xl font-bold my-10 text-orange-600 text-center">Our Sub-Services</h2>
    <div class="flex flex-wrap justify-center gap-8 px-4">
        <?php if (empty($subservices)): ?>
            <p class="text-lg text-gray-600">No sub-services found. Try adjusting your search or selecting a different category.</p>
        <?php else: ?>
            <?php foreach ($subservices as $subservice): ?>
                <div class="subservice-card w-72 bg-white/90 p-5 rounded-xl shadow-lg border border-transparent relative">
                    <img
                        src="../Uploads/services/<?php echo htmlspecialchars($subservice['photo']); ?>"
                        alt="<?php echo htmlspecialchars($subservice['subservice_name']); ?>"
                        class="w-full h-52 object-cover rounded-lg"
                    />
                    <form method="POST" action="/easy/customer/subservice.php" class="inline">
                        <input type="hidden" name="toggle_favorites" value="<?php echo $subservice['id']; ?>">
                        <button type="submit" class="favorites-button <?php echo in_array($subservice['id'], $favorites) ? 'remove' : 'add'; ?>">
                            <?php echo in_array($subservice['id'], $favorites) ? 'Remove from Favorites' : 'Add to Favorites'; ?>
                        </button>
                    </form>
                    <h3 class="mt-4 text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($subservice['subservice_name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Service:</strong> <?php echo htmlspecialchars($subservice['parent_category']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Staff:</strong>
                        <a href="staff_profile.php?staff=<?php echo urlencode($subservice['staff_name']); ?>" class="text-orange-600 underline">
                            <?php echo htmlspecialchars($subservice['staff_name']); ?>
                        </a>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Price:</strong> Rs <?php echo number_format($subservice['price_per_hour'], 2); ?>/hour
                    </p>
                    <button
                        class="glow-button mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 w-full"
                        onclick="openBookingForm('<?php echo htmlspecialchars($subservice['subservice_name']); ?>', '<?php echo htmlspecialchars($subservice['staff_name']); ?>', '<?php echo $subservice['id']; ?>')"
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
            <h2 class="text-2xl font-bold text-orange-600 mb-4">Book Service</h2>
            <form method="POST" action="/easy/customer/subservice.php">
                <input type="hidden" name="subservice_id" id="subserviceId">
                <input type="text" id="serviceName" name="subservice_name" readonly />
                <input type="text" id="workerName" name="staff_name" readonly />
                <input type="text" name="customer_name" placeholder="Your Name" required />
                <input type="text" name="address" placeholder="Address" required />
                <input type="tel" name="phone" placeholder="Phone Number" required />
                <input type="text" name="landmark" placeholder="Landmark" />
                <input type="email" name="email" placeholder="Email" />
                <textarea name="note" placeholder="Order Note"></textarea>
                <label for="bookingDate">Booking Date:</label>
                <input type="date" id="bookingDate" name="booking_date" required />
                <label for="bookingTime">Booking Time:</label>
                <div class="flex space-x-2">
                    <select name="hour" id="hour" required>
                        <option value="">Hour</option>
                        <?php for ($h = 1; $h <= 12; $h++): ?>
                            <option value="<?php echo sprintf('%02d', $h); ?>"><?php echo $h; ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="minute" id="minute" required>
                        <option value="">Minute</option>
                        <option value="00">00</option>
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="45">45</option>
                    </select>
                    <select name="period" id="period" required>
                        <option value="">AM/PM</option>
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                </div>
                <input type="hidden" name="booking_time" id="bookingTime">
                <div class="button-group">
                    <button type="submit" name="confirm_booking" class="confirm-btn">Confirm Booking</button>
                    <button type="button" class="close-btn" onclick="closeBookingForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize Swiper
        var swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 10,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 40,
                },
            },
        });

        // Display messages
        <?php if ($message): ?>
            alert("<?php echo addslashes($message); ?>");
        <?php endif; ?>
        <?php if ($booking_message): ?>
            alert("<?php echo addslashes($booking_message); ?>");
            window.location.href = '/easy/customer/subservice.php';
        <?php endif; ?>
        <?php if ($booking_error): ?>
            alert("<?php echo addslashes($booking_error); ?>");
        <?php endif; ?>

        // Search Functionality
        function searchServices() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let cards = document.getElementsByClassName("subservice-card");

            for (let card of cards) {
                let title = card.getElementsByTagName("h3")[0].innerText.toLowerCase();
                let category = card.querySelector("p strong").nextSibling.textContent.toLowerCase();
                if (title.includes(input) || category.includes(input)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            }

            // Update hidden form input for server-side search
            document.getElementById("searchFormInput").value = input;
        }

        // Open Booking Form
        function openBookingForm(service, worker, subserviceId) {
            const bookingContainer = document.getElementById("bookingContainer");
            document.getElementById("serviceName").value = service;
            document.getElementById("workerName").value = worker;
            document.getElementById("subserviceId").value = subserviceId;
            bookingContainer.style.visibility = "visible";
            bookingContainer.style.opacity = "1";
        }

        // Close Booking Form
        function closeBookingForm() {
            const bookingContainer = document.getElementById("bookingContainer");
            bookingContainer.style.visibility = "hidden";
            bookingContainer.style.opacity = "0";
        }

        // Format Time for Submission
        document.querySelector('form').addEventListener('submit', function (e) {
            const hour = document.getElementById('hour').value;
            const minute = document.getElementById('minute').value;
            const period = document.getElementById('period').value;

            if (hour && minute && period) {
                let hour24 = parseInt(hour);
                if (period === 'PM' && hour24 !== 12) {
                    hour24 += 12;
                } else if (period === 'AM' && hour24 === 12) {
                    hour24 = 0;
                }
                const time = `${hour24.toString().padStart(2, '0')}:${minute}:00`;
                document.getElementById('bookingTime').value = time;
            } else {
                e.preventDefault();
                alert('Please select a valid time.');
            }
        });
    </script>
</body>
</html>