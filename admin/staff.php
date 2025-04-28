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

// Initialize message
$message = '';

// Handle Add Staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $staff_id = trim($_POST['staff_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $joined_date = trim($_POST['joined_date'] ?? '');
    $parent_service_id = $_POST['parent_service'] ?? '';
    $subservice_ids = $_POST['subservices'] ?? [];
    $custom_subservice = trim($_POST['custom_subservice'] ?? '');

    // Validate form data
    if (empty($staff_id) || empty($name) || empty($email) || empty($phone_number) || empty($joined_date) || empty($parent_service_id) || (empty($subservice_ids) && empty($custom_subservice))) {
        $message = "All required fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        $message = "Phone number must be 10 digits.";
    } elseif (strlen($description) > 500) {
        $message = "Description cannot exceed 500 characters.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $joined_date) || strtotime($joined_date) > time()) {
        $message = "Invalid or future join date.";
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
                // Insert staff details
                $stmt = $conn->prepare("INSERT INTO staff (staff_id, name, email, phone_number, description, joined_date) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $message = "Database error: " . $conn->error;
                } else {
                    $stmt->bind_param("ssssss", $staff_id, $name, $email, $phone_number, $description, $joined_date);
                    if ($stmt->execute()) {
                        $staff_id_inserted = $conn->insert_id;

                        // Handle custom subservice
                        if (!empty($custom_subservice)) {
                            $sub_stmt = $conn->prepare("SELECT id FROM subservices WHERE subservice_name = ? AND parent_service_id = ?");
                            if ($sub_stmt) {
                                $sub_stmt->bind_param("si", $custom_subservice, $parent_service_id);
                                $sub_stmt->execute();
                                $sub_result = $sub_stmt->get_result();
                                if ($sub_result->num_rows > 0) {
                                    $subservice_ids[] = $sub_result->fetch_assoc()['id'];
                                } else {
                                    $insert_stmt = $conn->prepare("INSERT INTO subservices (subservice_name, parent_service_id) VALUES (?, ?)");
                                    if ($insert_stmt) {
                                        $insert_stmt->bind_param("si", $custom_subservice, $parent_service_id);
                                        if ($insert_stmt->execute()) {
                                            $subservice_ids[] = $conn->insert_id;
                                        } else {
                                            $message = "Error adding custom subservice: " . $conn->error;
                                        }
                                        $insert_stmt->close();
                                    }
                                }
                                $sub_stmt->close();
                            }
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
                            $conn->query("DELETE FROM staff WHERE id = $staff_id_inserted");
                        }
                    } else {
                        $message = "Error adding staff: " . $conn->error;
                    }
                }
                $stmt->close();
            }
        }
    }
}

// Handle Delete Staff
if (isset($_POST['delete_staff'])) {
    $staff_id = $_POST['delete_staff'];
    $delete_stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
    if ($delete_stmt) {
        $delete_stmt->bind_param("i", $staff_id);
        if ($delete_stmt->execute()) {
            $conn->query("DELETE FROM staff_subservices WHERE staff_id = $staff_id");
            $message = "Staff deleted successfully!";
        } else {
            $message = "Error deleting staff: " . $conn->error;
        }
        $delete_stmt->close();
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
    SELECT s.id, s.staff_id, s.name, s.email, s.phone_number, s.description, s.joined_date, 
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
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            min-height: 100vh;
            color: #2c3e50;
        }
        .navbar {
            background: #2c3e50;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar .back-to-dashboard a {
            text-decoration: none;
            color: #f1c40f;
            font-weight: 600;
            font-size: 18px;
            transition: color 0.3s;
        }
        .navbar .back-to-dashboard a:hover {
            color: #e67e22;
        }
        .navbar .back-to-dashboard a i {
            margin-right: 10px;
        }
        .navbar .nav-links a {
            text-decoration: none;
            background: linear-gradient(to right, #1abc9c, #2ecc71);
            color: #fff;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(26, 188, 156, 0.3);
        }
        .navbar .nav-links a:hover {
            transform: scale(1.1);
            background: linear-gradient(to right, #2ecc71, #1abc9c);
            box-shadow: 0 6px 20px rgba(26, 188, 156, 0.5);
        }
        .navbar .nav-links a i {
            margin-right: 10px;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .staff-section {
            background: #ffffff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: slideUp 1s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .staff-section h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
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
            border: 1px solid #dfe6e9;
            border-radius: 25px;
            background: #f9fbfd;
            color: #2c3e50;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .search-bar input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(26, 188, 156, 0.3);
            background: #ffffff;
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
            color: #2ecc71;
        }
        .staff-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .staff-card {
            background: linear-gradient(135deg, #ffffff, #f9fbfd);
            border: 1px solid #dfe6e9;
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .staff-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .staff-card h3 {
            color: #f1c40f;
            font-size: 18px;
            font-weight: 500;
        }
        .staff-card p {
            color: #34495e;
            font-size: 14px;
            margin: 5px 0;
        }
        .staff-card .actions {
            display: flex;
            justify-content: flex-end;
            margin-top: auto;
        }
        .staff-card .actions button {
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
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: fadeIn 0.4s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-content h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
        }
        .modal-content form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .modal-content .full-width {
            grid-column: span 2;
        }
        .modal-content label {
            color: #2c3e50;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 4px;
            display: block;
        }
        .modal-content input, .modal-content select, .modal-content textarea {
            padding: 8px;
            border: 1px solid #dfe6e9;
            border-radius: 6px;
            background: #f9fbfd;
            color: #2c3e50;
            font-size: 13px;
            transition: all 0.3s ease;
            width: 100%;
        }
        .modal-content input:focus, | .modal-content select:focus, .modal-content textarea:focus {
            outline: none;
            box-shadow: 0 0 8px rgba(26, 188, 156, 0.3);
            background: #ffffff;
        }
        .modal-content textarea {
            resize: vertical;
            min-height: 80px;
        }
        .modal-content select[multiple] {
            height: 80px;
        }
        .modal-content button {
            background: linear-gradient(to right, #1abc9c, #2ecc71);
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 600;
            grid-column: span 2;
        }
        .modal-content button:hover {
            background: linear-gradient(to right, #2ecc71, #1abc9c);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 188, 156, 0.4);
        }
        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #2c3e50;
            font-size: 20px;
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
                <form action="staff.php" method="GET">
                    <input type="text" name="search" placeholder="Search staff by ID or name..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="staff-list">
                <?php if ($staff_list_result && $staff_list_result->num_rows > 0) : ?>
                    <?php while ($staff = $staff_list_result->fetch_assoc()) : ?>
                        <div class="staff-card">
                            <h3><?php echo htmlspecialchars($staff['name']); ?></h3>
                            <p><strong>Staff ID:</strong> <?php echo htmlspecialchars($staff['staff_id']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($staff['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($staff['phone_number']); ?></p>
                            <p><strong>Joined:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($staff['joined_date']))); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($staff['description'] ?: 'No description provided'); ?></p>
                            <p><strong>Subservices:</strong> <?php echo htmlspecialchars($staff['subservices'] ?: 'None'); ?></p>
                            <div class="actions">
                                <form action="staff.php" method="POST" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($staff['name']); ?>?')">
                                    <input type="hidden" name="delete_staff" value="<?php echo $staff['id']; ?>">
                                    <button type="submit" class="delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p style="text-align: center; color: #2c3e50;">No staff found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addStaffModal')">×</span>
            <h3>Add New Staff</h3>
            <form action="staff.php" method="POST">
                <input type="hidden" name="add_staff" value="1">
                <div>
                    <label for="staff_id">Staff ID</label>
                    <input type="text" name="staff_id" id="staff_id" placeholder="Unique staff ID" required>
                </div>
                <div>
                    <label for="name">Staff Name</label>
                    <input type="text" name="name" id="name" placeholder="Staff name" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Staff email" required>
                </div>
                <div>
                    <label for="phone_number">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number" placeholder="10-digit phone" pattern="[0-9]{10}" required>
                </div>
                <div>
                    <label for="joined_date">Joined Easy Living</label>
                    <input type="date" name="joined_date" id="joined_date" max="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="full-width">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" placeholder="Staff description (max 500 chars)" maxlength="500"></textarea>
                </div>
                <div>
                    <label for="parent_service">Service Category</label>
                    <select name="parent_service" id="parent_service" onchange="loadSubservices()" required>
                        <option value="">Select service</option>
                        <?php foreach ($services as $service) : ?>
                            <option value="<?php echo $service['id']; ?>">
                                <?php echo htmlspecialchars($service['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="subservices">Subservices</label>
                    <select name="subservices[]" id="subservices" multiple>
                        <option value="">Select subservices</option>
                    </select>
                </div>
                <div class="full-width">
                    <label for="custom_subservice">Custom Subservice (optional)</label>
                    <input type="text" name="custom_subservice" id="custom_subservice" placeholder="New subservice (e.g., Special Massage)">
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
            document.getElementById('description').value = '';
            document.getElementById('custom_subservice').value = '';
            document.getElementById('subservices').innerHTML = '<option value="">Select subservices</option>';
        }
        function loadSubservices() {
            const parentServiceId = document.getElementById('parent_service').value;
            const subserviceSelect = document.getElementById('subservices');
            if (!parentServiceId) {
                subserviceSelect.innerHTML = '<option value="">Select subservices</option>';
                return;
            }
            fetch(`get_subservices.php?parent_service_id=${parentServiceId}`)
                .then(response => response.json())
                .then(data => {
                    subserviceSelect.innerHTML = '<option value="">Select subservices</option>';
                    data.forEach(subservice => {
                        const option = document.createElement('option');
                        option.value = subservice.id;
                        option.textContent = subservice.subservice_name;
                        subserviceSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching subservices:', error));
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