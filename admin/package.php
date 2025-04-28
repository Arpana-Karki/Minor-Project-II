<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
include('../db_connection.php');

// Check if connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create Uploads directory if it doesn't exist
$target_dir = __DIR__ . "/Uploads/";
if (!file_exists($target_dir)) {
    if (!mkdir($target_dir, 0755, true) && !is_dir($target_dir)) {
        error_log("Failed to create directory: $target_dir");
        die("Error: Cannot create Uploads directory. Please check folder permissions.");
    }
}

if (!is_writable($target_dir)) {
    error_log("Directory is not writable: $target_dir");
    die("Error: Uploads directory is not writable. Please check permissions.");
}

// Fetch Staff for Dropdown
// NEW: Query to get all staff members
$sql_staff = "SELECT id, name FROM staff";
$result_staff = $conn->query($sql_staff);
if (!$result_staff) {
    error_log("Failed to fetch staff: " . $conn->error);
    die("Error: Cannot fetch staff data.");
}

// Handle Add Package
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Check if form data is received
    if (isset($_POST['add_package'])) {
        echo "Form submitted successfully.<br>";
        var_dump($_POST, $_FILES); // Debug: Display form data
    } else {
        echo "Error: add_package not set in POST data.<br>";
        var_dump($_POST); // Debug: Show what was received
        die();
    }

    // Sanitize and validate inputs
    $name = trim(strip_tags($_POST['package_name']));
    $description = trim(strip_tags($_POST['package_description']));
    $price = filter_input(INPUT_POST, 'package_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_SANITIZE_NUMBER_INT); // NEW: Get staff_id
    $image = preg_replace("/[^A-Za-z0-9._-]/", "", basename($_FILES['package_image']['name']));
    $target_file = $target_dir . $image;

    // Validate inputs
    if (empty($name) || strlen($name) > 255) {
        die("Error: Package name is required and must be 255 characters or less.");
    }
    if (empty($description) || strlen($description) > 1000) {
        die("Error: Description is required and must be 1000 characters or less.");
    }
    if ($price === false || $price < 0) {
        die("Error: Invalid price value.");
    }
    if (empty($staff_id) || $staff_id <= 0) { // NEW: Validate staff_id
        die("Error: Please select a valid staff member.");
    }

    // Validate file type and size
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    if (!isset($_FILES['package_image']) || $_FILES['package_image']['error'] === UPLOAD_ERR_NO_FILE) {
        die("Error: No image file provided.");
    }
    if (!in_array($_FILES['package_image']['type'], $allowed_types)) {
        die("Error: Only JPEG, PNG, and GIF files are allowed.");
    }
    if ($_FILES['package_image']['size'] > $max_size) {
        die("Error: File size exceeds 2MB limit.");
    }

    if (!empty($image)) {
        if (move_uploaded_file($_FILES['package_image']['tmp_name'], $target_file)) {
            echo "Image uploaded successfully.<br>"; // Debug
            // NEW: Updated query to include staff_id
            $sql = "INSERT INTO packages (name, description, price, image, staff_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssdsi", $name, $description, $price, $image, $staff_id); // NEW: Bind staff_id
                if ($stmt->execute()) {
                    echo "Package inserted into database successfully.<br>"; // Debug
                } else {
                    error_log("Failed to insert package: " . $stmt->error);
                    die("Error: Failed to save package to database. " . $stmt->error);
                }
                $stmt->close();
            } else {
                error_log("Failed to prepare statement: " . $conn->error);
                die("Error: Database prepare error: " . $conn->error);
            }
        } else {
            $upload_error = $_FILES['package_image']['error'];
            error_log("File upload error code: $upload_error");
            die("Error: Failed to upload image. Error code: $upload_error");
        }
    } else {
        die("Error: Invalid image file name.");
    }

    // Redirect to avoid form resubmission
    header("Location: package.php");
    exit();
}

// Handle Delete Package
if (isset($_GET['delete'])) {
    $id = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);
    $sql = "DELETE FROM packages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            error_log("Failed to delete package: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare delete statement: " . $conn->error);
    }
    // Redirect to refresh the page
    header("Location: package.php");
    exit();
}

// Fetch Packages
// NEW: Join with staff table to display staff name in the table
$sql = "SELECT p.*, s.name AS staff_name FROM packages p LEFT JOIN staff s ON p.staff_id = s.id";
$result = $conn->query($sql);
if (!$result) {
    error_log("Failed to fetch packages: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Packages | Easy Living</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Existing styles unchanged */
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #ffffff;
            border-radius: 15px;
            padding: 40px;
            width: 90%;
            max-width: 1200px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            animation: slideUp 1s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h1 {
            text-align: center;
            color: #1a202c;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .back-btn {
            display: inline-block;
            background: #4a90e2;
            color: #fff;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-btn:hover {
            background: #357abd;
            transform: scale(1.05);
        }

        .add-btn {
            display: inline-block;
            background: #4a90e2;
            color: #fff;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            background: #357abd;
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #f9fafb;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            color: #1a202c;
        }

        th {
            background: #edf2f7;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background: #f1f5f9;
        }

        .action-btn {
            color: #fff;
            padding: 8px 15px;
            border-radius: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .delete-btn {
            background: #e53e3e;
        }

        .delete-btn:hover {
            background: #c53030;
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
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: fadeIn 0.5s ease;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #1a202c;
            font-size: 24px;
            cursor: pointer;
        }

        .popup-content h2 {
            color: #1a202c;
            margin-bottom: 20px;
            text-align: center;
        }

        .popup-content label {
            color: #1a202c;
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .popup-content input, .popup-content textarea, .popup-content select { /* NEW: Added select */
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            background: #f9fafb;
            color: #1a202c;
        }

        .popup-content input[type="file"] {
            padding: 5px;
            border: none;
        }

        .popup-content button {
            background: #4a90e2;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .popup-content button:hover {
            background: #357abd;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dash.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <h1>Manage Packages</h1>
        <a href="#" class="add-btn" onclick="openPopup()"><i class="fas fa-plus"></i> Add Package</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Staff</th> <!-- NEW: Added Staff column -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo number_format($row['price'], 2); ?></td>
                            <td><img src="/easy/admin/Uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 50px; height: auto;"></td>
                            <td><?php echo htmlspecialchars($row['staff_name'] ?? 'None'); ?></td> <!-- NEW: Display staff name -->
                            <td>
                                <a href="?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this package?')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center;">No packages found.</td></tr> <!-- NEW: Updated colspan -->
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Popup Form -->
    <div id="addPackagePopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup()">×</span>
            <h2>Add New Package</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="package_name">Package Name</label>
                <input type="text" id="package_name" name="package_name" required>
                
                <label for="package_description">Description</label>
                <textarea id="package_description" name="package_description" required></textarea>
                
                <label for="package_price">Price</label>
                <input type="number" id="package_price" name="package_price" step="0.01" required>
                
                <label for="staff_id">Staff</label> <!-- NEW: Staff dropdown -->
                <select id="staff_id" name="staff_id" required>
                    <option value="">Select Staff</option>
                    <?php if ($result_staff && $result_staff->num_rows > 0): ?>
                        <?php while ($staff = $result_staff->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($staff['id']); ?>">
                                <?php echo htmlspecialchars($staff['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No staff available</option>
                    <?php endif; ?>
                </select>
                
                <label for="package_image">Image</label>
                <input type="file" id="package_image" name="package_image" accept="image/jpeg,image/png,image/gif" required>
                
                <button type="submit" name="add_package">Add Package</button>
            </form>
        </div>
    </div>

    <script>
        function openPopup() {
            document.getElementById('addPackagePopup').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('addPackagePopup').style.display = 'none';
        }
    </script>
</body>
</html>
<?php 
// NEW: Free the staff result and close connection
$result_staff->free();
$conn->close(); 
?>