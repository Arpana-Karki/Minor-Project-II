<?php
// Enable error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

session_start();

// Check admin login
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
try {
    include '../db_connection.php';
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("<div style='text-align: center; padding: 20px; color: red;'>Error: Unable to connect to the database. Please check db_connection.php.</div>");
}

// Set GROUP_CONCAT max length
$conn->query("SET SESSION group_concat_max_len = 10000");

// Define upload directory
$upload_dir = '/Applications/XAMPP/xamppfiles/htdocs/easy/uploads/staff/';
$upload_url = '/easy/uploads/staff/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        error_log("Failed to create directory: $upload_dir");
        die("<div style='text-align: center; padding: 20px; color: red;'>Error: Unable to create upload directory.</div>");
    }
}
if (!is_writable($upload_dir)) {
    error_log("Upload directory is not writable: $upload_dir");
    die("<div style='text-align: center; padding: 20px; color: red;'>Error: Upload directory is not writable.</div>");
}

// Initialize message
$message = '';

// Handle Add Staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $staff_id = trim($_POST['staff_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $subservices_input = trim($_POST['subservices'] ?? '');
    $parent_service_id = $_POST['parent_service'] ?? '';
    
    // Validate form data
    if (empty($staff_id) || empty($name) || empty($email) || empty($phone_number) || empty($subservices_input) || empty($parent_service_id) || !isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        $message = "Phone number must be 10 digits.";
    } elseif ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $message = "Photo upload failed: Error code " . $_FILES['photo']['error'];
    } else {
        // Parse subservices (comma-separated)
        $subservices = array_filter(array_map('trim', explode(',', $subservices_input)));
        if (count($subservices) > 5) {
            $message = "Maximum 5 subservices can be assigned.";
        } elseif (count($subservices) === 0) {
            $message = "At least one subservice is required.";
        } else {
            // Check if staff_id is unique
            $stmt = $conn->prepare("SELECT id FROM staff WHERE staff_id = ?");
            if (!$stmt) {
                $message = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param("s", $staff_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $message = "Staff ID already exists.";
                } else {
                    // Handle file upload
                    $photo = $_FILES['photo']['name'];
                    $photo_temp = $_FILES['photo']['tmp_name'];
                    $ext = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
                    $sanitized_photo = uniqid('staff_', true) . '.' . $ext;
                    $photo_dir = $upload_dir . $sanitized_photo;

                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    $file_info = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($file_info, $photo_temp);

                    if (!in_array($mime_type, $allowed_types) || !in_array($ext, $allowed_exts)) {
                        $message = "Only JPEG, PNG, or GIF files are allowed.";
                    } elseif ($_FILES['photo']['size'] > $max_size) {
                        $message = "File size exceeds 5MB.";
                    } elseif (!move_uploaded_file($photo_temp, $photo_dir)) {
                        $message = "Failed to upload photo. Check directory permissions.";
                    } else {
                        // Insert staff details
                        $stmt = $conn->prepare("INSERT INTO staff (staff_id, name, email, phone_number, photo) VALUES (?, ?, ?, ?, ?)");
                        if (!$stmt) {
                            $message = "Database error: " . $conn->error;
                            unlink($photo_dir);
                        } else {
                            $stmt->bind_param("sssss", $staff_id, $name, $email, $phone_number, $sanitized_photo);
                            if ($stmt->execute()) {
                                $staff_id_inserted = $conn->insert_id;

                                // Process subservices
                                $subservice_ids = [];
                                foreach ($subservices as $subservice_name) {
                                    // Check if subservice exists
                                    $sub_stmt = $conn->prepare("SELECT id FROM subservices WHERE subservice_name = ? AND parent_service_id = ?");
                                    if (!$sub_stmt) {
                                        $message = "Database error: " . $conn->error;
                                        break;
                                    }
                                    $sub_stmt->bind_param("si", $subservice_name, $parent_service_id);
                                    $sub_stmt->execute();
                                    $sub_result = $sub_stmt->get_result();
                                    if ($sub_result->num_rows > 0) {
                                        $subservice_ids[] = $sub_result->fetch_assoc()['id'];
                                    } else {
                                        // Insert new subservice
                                        $insert_stmt = $conn->prepare("INSERT INTO subservices (subservice_name, parent_service_id) VALUES (?, ?)");
                                        if (!$insert_stmt) {
                                            $message = "Database error: " . $conn->error;
                                            break;
                                        }
                                        $insert_stmt->bind_param("si", $subservice_name, $parent_service_id);
                                        if ($insert_stmt->execute()) {
                                            $subservice_ids[] = $conn->insert_id;
                                        } else {
                                            $message = "Error adding subservice: " . $conn->error;
                                            break;
                                        }
                                        $insert_stmt->close();
                                    }
                                    $sub_stmt->close();
                                }

                                // Insert staff-subservice mappings
                                if (empty($message) && !empty($subservice_ids)) {
                                    $subservice_stmt = $conn->prepare("INSERT INTO staff_subservices (staff_id, subservice_id) VALUES (?, ?)");
                                    if (!$subservice_stmt) {
                                        $message = "Database error: " . $conn->error;
                                    } else {
                                        foreach ($subservice_ids as $subservice_id) {
                                            $subservice_stmt->bind_param("ii", $staff_id_inserted, $subservice_id);
                                            if (!$subservice_stmt->execute()) {
                                                $message = "Error linking subservice: " . $conn->error;
                                                break;
                                            }
                                        }
                                        $subservice_stmt->close();
                                    }
                                }

                                if (empty($message)) {
                                    $message = "Staff added successfully!";
                                } else {
                                    // Rollback: delete staff and photo
                                    $conn->query("DELETE FROM staff WHERE id = $staff_id_inserted");
                                    unlink($photo_dir);
                                }
                            } else {
                                $message = "Error adding staff: " . $conn->error;
                                unlink($photo_dir);
                            }
                        }
                    }
                    finfo_close($file_info);
                }
                $stmt->close();
            }
        }
    }
}

// Handle Delete Staff
if (isset($_POST['delete_staff'])) {
    $staff_id = $_POST['delete_staff'];
    $stmt = $conn->prepare("SELECT photo FROM staff WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $staff = $result->fetch_assoc();
            $photo_path = $upload_dir . $staff['photo'];
            
            $delete_stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
            if ($delete_stmt) {
                $delete_stmt->bind_param("i", $staff_id);
                if ($delete_stmt->execute()) {
                    $conn->query("DELETE FROM staff_subservices WHERE staff_id = $staff_id");
                    if (file_exists($photo_path)) {
                        unlink($photo_path);
                    }
                    $message = "Staff deleted successfully!";
                } else {
                    $message = "Error deleting staff: " . $conn->error;
                }
                $delete_stmt->close();
            } else {
                $message = "Database error: " . $conn->error;
            }
        } else {
            $message = "Staff not found.";
        }
        $stmt->close();
    } else {
        $message = "Database error: " . $conn->error;
    }
}

// Fetch services for selection
$services_query = "SELECT id, category FROM services";
$services_result = $conn->query($services_query);
$services = [];
if ($services_result) {
    while ($service = $services_result->fetch_assoc()) {
        $services[] = $service;
    }
} else {
    $message = "Error fetching services: " . $conn->error;
}

// Fetch staff with search
$search = trim($_GET['search'] ?? '');
$search_param = "%$search%";
$stmt = $conn->prepare("
    SELECT s.id, s.staff_id, s.name, s.email, s.phone_number, s.photo, 
           GROUP_CONCAT(ss.subservice_name SEPARATOR ', ') AS subservices 
    FROM staff s 
    LEFT JOIN staff_subservices sts ON s.id = sts.staff_id 
    LEFT JOIN subservices ss ON sts.subservice_id = ss.id 
    WHERE s.staff_id LIKE ? OR s.name LIKE ? 
    GROUP BY s.id 
    ORDER BY s.name");
if (!$stmt) {
    $message = "Database error: " . $conn->error;
} else {
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $staff_list_result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff | Easy Living</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background: linear-gradient(135deg, #1a1a1a, #2c3e50);
            min-height: 100vh;
            color: #ecf0f1;
        }
        .navbar {
            background: rgba(44, 62, 80, 0.98);
            padding: 15px 30 getter: nowrappx;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar .back-to-dashboard a {
            text-decoration: none;
            color: #e8b923;
            font-weight: 600;
            font-size: 16px;
            transition: color 0.3s;
        }
        .navbar .back-to-dashboard a:hover {
            color: #d4a017;
        }
        .navbar .back-to-dashboard a i {
            margin-right: 8px;
        }
        .navbar .nav-links {
            display: flex;
            gap: 20px;
        }
        .navbar .nav-links a {
            text-decoration: none;
            background: linear-gradient(to right, #1abc9c, #16a085);
            color: #fff;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .navbar .nav-links a:hover {
            transform: scale(1.05);
            background: linear-gradient(to right, #16a085, #1abc9c);
        }
        .navbar .nav-links a i {
            margin-right: 8px;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .staff-section {
            background: rgba(44, 62, 80, 0.98);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            animation: slideUp 1s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .staff-section h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ecf0f1;
            font-weight: 600;
            font-size: 28px;
        }
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .search-bar form {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 500px;
            position: relative;
        }
        .search-bar input {
            width: 100%;
            padding: 12px 40px 12px 20px;
            border: none;
            border-radius: 25px;
            background: #34495e;
            color: #ecf0f1;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .search-bar input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(26, 188, 156, 0.5);
            background: #3e5c76;
        }
        .search-bar button {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #1abc9c;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .search-bar button:hover {
            color: #16a085;
        }
        .staff-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .staff-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .staff-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }
        .staff-card img {
            max-width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8 WARN: nowrappx;
            border: 2px solid #34495e;
        }
        .staff-card h3 {
            color: #e8b923;
            font-size: 18px;
            font-weight: 500;
        }
        .staff-card p {
            color: #bdc3c7;
            font-size: 14px;
            margin: 5px 0;
        }
        .staff-card .actions {
            display: flex;
            justify-content: flex-end;
            margin-top: auto;
        }
        .staff-card .actions button {
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            transition: all 0.3s ease;
            background: #e74c3c;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .staff-card .actions button:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(231, 76, 60, 0.4);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        .modal-content {
            background: #2c3e50;
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 700px;
            position: relative;
            animation: fadeIn 0.4s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-content h3 {
            color: #e8b923;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
        }
        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .modal-content label {
            color: #ecf0f1;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        .modal-content input, .modal-content select {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #34495e;
            color: #ecf0f1;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
        }
        .modal-content input:focus, .modal-content select:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(26, 188, 156, 0.5);
            background: #3e5c76;
        }
        .modal-content input[type="file"] {
            background: none;
            padding: 0;
        }
        .modal-content .file-upload-container {
            position: relative;
            text-align: center;
        }
        .modal-content .file-upload-label {
            background: linear-gradient(to right, rgb(223, 87, 28), rgb(226, 106, 50));
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            transition: background 0.3s;
            font-size: 14px;
            font-weight: 500;
        }
        .modal-content .file-upload-label:hover {
            background: linear-gradient(to right, rgb(231, 106, 53), rgb(219, 116, 47));
        }
        .modal-content .file-preview {
            margin-top: 15px;
            text-align: center;
        }
        .modal-content .file-preview img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #34495e;
        }
        .modal-content button {
            background: linear-gradient(to right, #1abc9c, #16a085);
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
            font-weight: 600;
        }
        .modal-content button:hover {
            background: linear-gradient(to right, #16a085, #1abc9c);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 188, 156, 0.4);
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #ecf0f1;
            font-size: 24px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .close-modal:hover {
            color: #e74c3c;
        }
        .message {
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 16px;
        }
        .message.success {
            background: #1abc9c;
            color: #fff;
        }
        .message.error {
            background: #e74c3c;
            color: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="back-to-dashboard">
            <a href="dash.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <div class="nav-links">
            <a href="#" onclick="openModal('addStaffModal')"><i class="fas fa-plus"></i> Add Staff</a>
        </div>
    </nav>
    <div class="container">
        <div class="staff-section">
            <h2>Manage Staff</h2>
            <?php if (!empty($message)) : ?>
                <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <div class="search-bar">
                <form action="newstaff.php" method="GET">
                    <input type="text" name="search" placeholder="Search staff by ID or name..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="staff-list">
                <?php if ($staff_list_result && $staff_list_result->num_rows > 0) : ?>
                    <?php while ($staff = $staff_list_result->fetch_assoc()) : ?>
                        <div class="staff-card">
                            <img src="<?php echo htmlspecialchars($upload_url . $staff['photo']); ?>" alt="<?php echo htmlspecialchars($staff['name']); ?>">
                            <h3><?php echo htmlspecialchars($staff['name']); ?></h3>
                            <p><strong>Staff ID:</strong> <?php echo htmlspecialchars($staff['staff_id']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($staff['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($staff['phone_number']); ?></p>
                            <p><strong>Subservices:</strong> <?php echo htmlspecialchars($staff['subservices'] ?: 'None'); ?></p>
                            <div class="actions">
                                <form action="newstaff.php" method="POST" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($staff['name']); ?>?')">
                                    <input type="hidden" name="delete_staff" value="<?php echo $staff['id']; ?>">
                                    <button type="submit" class="delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p style="text-align: center; color: #ecf0f1;">No staff found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addStaffModal')">×</span>
            <h3>Add New Staff</h3>
            <form action="staff.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_staff" value="1">
                <div>
                    <label for="staff_id">Staff ID</label>
                    <input type="text" name="staff_id" id="staff_id" placeholder="Enter unique staff ID" required>
                </div>
                <div>
                    <label for="name">Staff Name</label>
                    <input type="text" name="name" id="name" placeholder="Enter staff name" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Enter staff email" required>
                </div>
                <div>
                    <label for="phone_number">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number" placeholder="Enter 10-digit phone number" pattern="[0-9]{10}" required>
                </div>
                <div>
                    <label for="parent_service">Service Category</label>
                    <select name="parent_service" id="parent_service" required>
                        <option value="">Select a service</option>
                        <?php foreach ($services as $service) : ?>
                            <option value="<?php echo $service['id']; ?>">
                                <?php echo htmlspecialchars($service['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="subservices">Subservices (comma-separated)</label>
                    <input type="text" name="subservices" id="subservices" placeholder="e.g., Cleaning, Cooking, Gardening" required>
                </div>
                <div class="file-upload-container">
                    <label for="photo" class="file-upload-label">Upload Staff Photo</label>
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/gif" onchange="previewImage(this, 'preview-img-staff')" required>
                    <div class="file-preview">
                        <img id="preview-img-staff" src="" alt="Selected Image Preview" style="display: none;">
                    </div>
                </div>
                <button type="submit">Add Staff</button>
            </form>
        </div>
    </div>
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.getElementById('preview-img-staff').style.display = 'none';
            document.getElementById('photo').value = '';
        }
        function previewImage(input, imgId) {
            const previewImg = document.getElementById(imgId);
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onloadend = function () {
                    previewImg.src = reader.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.style.display = 'none';
            }
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal('addStaffModal');
            }
        };
    </script>
</body>
</html>
<?php
// Clean up
if (isset($stmt)) {
    $stmt->close();
}
if (isset($staff_list_result)) {
    $staff_list_result->free();
}
$conn->close();
?>