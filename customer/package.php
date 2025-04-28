<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../db_connection.php');

$customer_id = $_SESSION['customer_id'] ?? 1;
$customer_id = (int)$customer_id;
$_SESSION['favorites'] ??= [];

$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorites'])) {
    $subservice_id = (int)$_POST['toggle_favorites'];

    $stmt = mysqli_prepare($conn, "SELECT id FROM favorites WHERE customer_id = ? AND subservice_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $customer_id, $subservice_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $del = mysqli_prepare($conn, "DELETE FROM favorites WHERE customer_id = ? AND subservice_id = ?");
            mysqli_stmt_bind_param($del, 'ii', $customer_id, $subservice_id);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO favorites (customer_id, subservice_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins, 'ii', $customer_id, $subservice_id);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    // Sanitize and validate inputs
    $subservice_id = isset($_POST['subservice_id']) ? (int)$_POST['subservice_id'] : 0;
    $staff_id = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : 0;
    $subservice_name = mysqli_real_escape_string($conn, $_POST['subservice_name'] ?? '');
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $landmark = !empty($_POST['landmark']) ? mysqli_real_escape_string($conn, $_POST['landmark']) : null;
    $note = !empty($_POST['note']) ? mysqli_real_escape_string($conn, $_POST['note']) : null;
    $booking_datetime = $_POST['booking_datetime'] ?? '';

    $datetime = DateTime::createFromFormat('Y-m-d\TH:i', $booking_datetime);
    $booking_date = $datetime ? $datetime->format('Y-m-d') : null;
    $booking_time = $datetime ? $datetime->format('H:i:s') : null;

    $missing_fields = [];
    if (!$subservice_id) $missing_fields[] = 'subservice_id';
    if (!$staff_id) $missing_fields[] = 'staff_id';
    if (empty($subservice_name)) $missing_fields[] = 'subservice_name';
    if (empty($customer_name)) $missing_fields[] = 'customer_name';
    if (empty($address)) $missing_fields[] = 'address';
    if (empty($phone)) $missing_fields[] = 'phone';
    if (empty($booking_date)) $missing_fields[] = 'booking_date';
    if (empty($booking_time)) $missing_fields[] = 'booking_time';

    if (!empty($missing_fields)) {
        $booking_error = "Missing: " . implode(', ', $missing_fields);
    } else {
        $query = "INSERT INTO bookings (subservice_id, subservice_name, staff_id, customer_name, address, phone, landmark, note, booking_date, booking_time, customer_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isisssssssi', 
            $subservice_id, 
            $subservice_name, 
            $staff_id, 
            $customer_name, 
            $address, 
            $phone, 
            $landmark, 
            $note, 
            $booking_date, 
            $booking_time, 
            $customer_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo "<script>alert('Booking Confirmed!'); window.location.href='my_bookings.php';</script>";
        exit();
    }
}

// Fetch services
$parent_service_id = isset($_GET['parent_service_id']) ? (int)$_GET['parent_service_id'] : null;
$search = trim($_GET['search'] ?? '');

$subservices_query = "
    SELECT 
        ss.id, 
        ss.subservice_name, 
        ss.amount, 
        ss.photo, 
        s.category AS parent_category,
        GROUP_CONCAT(st.name SEPARATOR ', ') AS staff_names,
        GROUP_CONCAT(st.id) AS staff_ids
    FROM subservices ss
    JOIN services s ON ss.parent_service_id = s.id
    LEFT JOIN staff_subservices sts ON ss.id = sts.subservice_id
    LEFT JOIN staff st ON sts.staff_id = st.id
";

$conditions = [];
$params = [];
$types = '';

if ($parent_service_id) {
    $conditions[] = "ss.parent_service_id = ?";
    $params[] = $parent_service_id;
    $types .= 'i';
}
if ($search) {
    $conditions[] = "(ss.subservice_name LIKE ? OR s.category LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
if ($conditions) {
    $subservices_query .= " WHERE " . implode(' AND ', $conditions);
}
$subservices_query .= " GROUP BY ss.id ORDER BY ss.subservice_name";

$subservices = [];
$stmt = mysqli_prepare($conn, $subservices_query);
if ($stmt) {
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $row['first_staff_name'] = $row['staff_names'] ? explode(', ', $row['staff_names'])[0] : 'Not assigned';
        $row['first_staff_id'] = $row['staff_ids'] ? (int)explode(',', $row['staff_ids'])[0] : 0;
        $subservices[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    die('Query failed: ' . mysqli_error($conn));
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        .open-book-now-btn {
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
        .open-book-now-btn:hover {
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
    </style>
</head>
<body>

<?php if (!empty($booking_error)): ?>
    <div style="color: red; text-align: center; margin-bottom: 20px;">
        <?php echo htmlspecialchars($booking_error); ?>
    </div>
<?php endif; ?>

<div class="container">
    <?php if (!empty($subservices)): ?>
        <?php foreach ($subservices as $package): ?>
            <div class="package-card">
                <img src="../admin/uploads/<?php echo htmlspecialchars($package['photo']); ?>" alt="Package Image">
                <div class="package-details">
                    <h3><?php echo htmlspecialchars($package['subservice_name']); ?></h3>
                    <p class="price">Rs. <?php echo number_format((float)$package['amount'], 2); ?></p>
                    <p class="staff-info">Staff: <?php echo htmlspecialchars($package['first_staff_name']); ?></p>
                    <button class="open-book-now-btn"
                        data-package-id="<?php echo (int)$package['id']; ?>"
                        data-package-name="<?php echo htmlspecialchars($package['subservice_name']); ?>"
                        data-staff-name="<?php echo htmlspecialchars($package['first_staff_name']); ?>"
                        data-staff-id="<?php echo (int)$package['first_staff_id']; ?>">
                        Book Now
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No packages available at the moment.</p>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal" id="bookingModal">
    <div class="modal-content">
        <button class="close-btn" id="closeModal">X</button>
        <h2>Book Now</h2>
        <form method="post" id="bookingForm">
            <input type="hidden" name="subservice_id" id="modalPackageId" required>
            <input type="hidden" name="staff_id" id="modalStaffId" required>

            <input type="text" name="subservice_name" id="modalPackageName" readonly required>
            <input type="text" id="modalStaffName" readonly disabled>

            <input type="text" name="customer_name" placeholder="Your Name" required>
            <input type="text" name="address" placeholder="Your Address" required>
            <input type="tel" name="phone" placeholder="Your Phone" pattern="[0-9]{10}" required>
            <input type="text" name="landmark" placeholder="Landmark (Optional)">
            <textarea name="note" placeholder="Order Note (Optional)" rows="3"></textarea>
            <input type="datetime-local" name="booking_datetime" id="bookingDatetime" min="<?php echo date('Y-m-d\TH:i'); ?>" required>

            <button type="submit" name="confirm_booking" class="book-now-btn">Confirm Booking</button>
        </form>
    </div>
</div>

<script>
    // JavaScript to open modal and fill form fields
    const modal = document.getElementById('bookingModal');
    const closeModal = document.getElementById('closeModal');
    const openBookButtons = document.querySelectorAll('.open-book-now-btn');

    openBookButtons.forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('modalPackageId').value = button.dataset.packageId;
            document.getElementById('modalStaffId').value = button.dataset.staffId;
            document.getElementById('modalPackageName').value = button.dataset.packageName;
            document.getElementById('modalStaffName').value = "Staff: " + button.dataset.staffName;
            modal.style.display = 'flex';
        });
    });

    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
        document.getElementById('bookingForm').reset();
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.getElementById('bookingForm').reset();
        }
    });

</script>

</body>
</html>