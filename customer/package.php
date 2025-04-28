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

// Debug: Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Define dummy subservice_id for package bookings
define('DUMMY_SUBSERVICE_ID', 999); // Must match the ID inserted in subservices table

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    $package_id = isset($_POST['package_id']) ? (int)$_POST['package_id'] : 0;
    // Ensure staff_id is a referenceable variable
    $staff_id_temp = !empty($_POST['staff_id']) && (int)$_POST['staff_id'] > 0 ? (int)$_POST['staff_id'] : null;
    $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    // Ensure landmark and note are referenceable
    $landmark_temp = !empty($_POST['landmark']) ? mysqli_real_escape_string($conn, $_POST['landmark']) : null;
    $note_temp = !empty($_POST['order_note']) ? mysqli_real_escape_string($conn, $_POST['order_note']) : null;
    $booking_datetime = $_POST['booking_datetime'];
    $is_package = 1; // Indicate this is a package booking

    // Debug: Log package_id
    error_log("Received package_id: $package_id");

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
        'subservice_id' => DUMMY_SUBSERVICE_ID,
        'package_id' => $package_id,
        'staff_id' => $staff_id_temp,
        'subservice_name' => $package_name,
        'customer_name' => $customer_name,
        'address' => $address,
        'phone' => $phone,
        'landmark' => $landmark_temp,
        'note' => $note_temp,
        'booking_date' => $booking_date,
        'booking_time' => $booking_time,
        'customer_id' => $customer_id,
        'is_package' => $is_package
    ], true));

    // Validate required fields
    $missing_fields = [];
    if (empty($package_id) || $package_id <= 0) $missing_fields[] = 'package_id';
    if (empty($package_name)) $missing_fields[] = 'package_name';
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
            $query = "INSERT INTO bookings (subservice_id, subservice_name, staff_id, customer_name, address, phone, landmark, note, booking_date, booking_time, customer_id, is_package) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'isisssssssii', DUMMY_SUBSERVICE_ID, $package_name, $staff_id_temp, $customer_name, $address, $phone, $landmark_temp, $note_temp, $booking_date, $booking_time, $customer_id, $is_package);
            mysqli_stmt_execute($stmt);
            $booking_success = "Your booking is confirmed!";
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            $booking_error = "Failed to save booking: " . $e->getMessage();
            error_log("Booking error: " . $e->getMessage());
        }
    }
}

// Fetch packages and staff names
try {
    $packages_query = "
        SELECT 
            p.id, p.name AS package_name, p.description, p.price, p.image, p.staff_id, s.name AS staff_name
        FROM 
            packages p
        LEFT JOIN 
            staff s ON p.staff_id = s.id
        WHERE 
            p.id IS NOT NULL AND p.id > 0
    ";
    $packages_result = mysqli_query($conn, $packages_query);
    $packages = [];
    while ($package = mysqli_fetch_assoc($packages_result)) {
        // Ensure staff_id is 0 if NULL for display (not insertion)
        $package['staff_id'] = $package['staff_id'] ? (int)$package['staff_id'] : 0;
        $packages[] = $package;
    }
    // Debug: Log fetched packages
    error_log("Fetched packages: " . print_r($packages, true));
} catch (Exception $e) {
    die("Packages query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages - Easy Living</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .package-card {
            background: white;
            width: 300px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .package-card:hover {
            transform: translateY(-5px);
        }
        .package-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .package-details {
            padding: 15px;
        }
        .package-details h3 {
            margin: 0;
            font-size: 22px;
            color: #333;
        }
        .package-details p {
            color: #666;
            font-size: 14px;
            margin: 10px 0;
        }
        .price {
            font-weight: bold;
            color: green;
            font-size: 18px;
        }
        .staff-info {
            font-size: 14px;
            color: #444;
            font-weight: bold;
        }
        .book-now-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-top: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .book-now-btn:hover {
            background: #0056b3;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            position: relative;
        }
        .modal-content input,
        .modal-content textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .close-btn {
            background: red;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 10px;
            border-radius: 5px;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            text-align: center;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php if (isset($booking_success)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($booking_success); ?>
        </div>
    <?php elseif (isset($booking_error)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($booking_error); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <?php foreach ($packages as $package): ?>
            <div class="package-card">
                <img src="../admin/uploads/<?php echo htmlspecialchars($package['image']); ?>" alt="Package Image">
                <div class="package-details">
                    <h3><?php echo htmlspecialchars($package['package_name']); ?></h3>
                    <p><?php echo htmlspecialchars($package['description']); ?></p>
                    <p class="price">Rs. <?php echo htmlspecialchars($package['price']); ?></p>
                    <p class="staff-info">Staff: <?php echo htmlspecialchars($package['staff_name'] ?? 'Not Assigned'); ?></p>
                    <button class="book-now-btn" 
                            data-package-id="<?php echo htmlspecialchars($package['id']); ?>"
                            data-package-name="<?php echo htmlspecialchars($package['package_name']); ?>"
                            data-staff-name="<?php echo htmlspecialchars($package['staff_name'] ?? 'Not Assigned'); ?>"
                            data-staff-id="<?php echo htmlspecialchars($package['staff_id']); ?>">
                        Book Now
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal -->
    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <button class="close-btn" id="closeModal">X</button>
            <h2>Book Now</h2>
            <form method="post" id="bookingForm">
                <input type="hidden" name="package_id" id="modalPackageId">
                <input type="text" name="package_name" id="modalPackageName" readonly required>
                <input type="hidden" name="staff_id" id="modalStaffId">
                <input type="text" name="staff_name" id="modalStaffName" readonly required>
                <input type="text" name="customer_name" placeholder="Your Name" required>
                <input type="text" name="address" placeholder="Your Address" required>
                <input type="tel" name="phone" placeholder="Your Phone" pattern="[0-9]{10}" required>
                <input type="text" name="landmark" placeholder="Landmark (Optional)">
                <textarea name="order_note" placeholder="Order Note (Optional)" rows="3"></textarea>
                <input type="datetime-local" name="booking_datetime" id="bookingDatetime" min="2025-04-26T06:00" max="2025-12-31T21:00" required>
                <button type="submit" name="book_now" class="book-now-btn">Confirm Booking</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('bookingModal');
        const closeModal = document.getElementById('closeModal');
        const bookButtons = document.querySelectorAll('.book-now-btn');

        bookButtons.forEach(button => {
            button.addEventListener('click', function() {
                const packageId = this.dataset.packageId;
                const staffId = this.dataset.staffId;
                console.log('Opening modal with:', { packageId, staffId }); // Debug
                if (!packageId || packageId === '0' || isNaN(packageId)) {
                    alert('Invalid package ID.');
                    return;
                }
                document.getElementById('modalPackageId').value = packageId;
                document.getElementById('modalPackageName').value = this.dataset.packageName;
                document.getElementById('modalStaffName').value = this.dataset.staffName;
                document.getElementById('modalStaffId').value = staffId;
                modal.style.display = 'flex';
            });
        });

        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
            document.getElementById('bookingForm').reset();
        });

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                document.getElementById('bookingForm').reset();
            }
        };

        // Validate Datetime Input
        document.getElementById('bookingDatetime').addEventListener('change', function(e) {
            const datetime = new Date(e.target.value);
            const hours = datetime.getHours();
            const minutes = datetime.getMinutes();
            if (hours < 6 || hours > 21 || (hours === 21 && minutes > 0)) {
                alert('Please select a time between 6:00 AM and 9:00 PM.');
                e.target.value = '';
            }
        });

        // Validate Form Submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const packageId = document.getElementById('modalPackageId').value;
            const datetime = document.getElementById('bookingDatetime').value;
            console.log('Submitting form with:', { packageId, datetime }); // Debug
            if (!packageId || packageId === '0' || isNaN(packageId)) {
                e.preventDefault();
                alert('Invalid package ID.');
            }
            if (!datetime) {
                e.preventDefault();
                alert('Please select a valid date and time between 6:00 AM and 9:00 PM.');
            }
        });

        // Display success message and redirect
        <?php if (isset($booking_success)): ?>
            alert("<?php echo addslashes($booking_success); ?>");
            window.location.href = '/easy/customer/my_bookings.php';
        <?php endif; ?>
    </script>
</body>
</html>