<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}
include('../db_connection.php');

// Add Service
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $photo = $_FILES['photo']['name'];
    $photo_temp = $_FILES['photo']['tmp_name'];
    $photo_dir = '../Uploads/services/' . $photo;

    if (move_uploaded_file($photo_temp, $photo_dir)) {
        $query = "INSERT INTO services (category, description, photo) VALUES ('$category', '$description', '$photo')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Service added successfully!'); window.location.href = 'service.php';</script>";
        } else {
            echo "<script>alert('Error adding service.');</script>";
        }
    } else {
        echo "<script>alert('Failed to upload photo.');</script>";
    }
}

// Add Subservice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subservice'])) {
    $parent_service_id = mysqli_real_escape_string($conn, $_POST['parent_service_id']);
    $subservice_name = mysqli_real_escape_string($conn, $_POST['subservice_name']);
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $photo = $_FILES['photo']['name'];
    $photo_temp = $_FILES['photo']['tmp_name'];
    $photo_dir = '../Uploads/services/' . $photo;

    if (move_uploaded_file($photo_temp, $photo_dir)) {
        $query = "INSERT INTO subservices (parent_service_id, subservice_name, staff_name, price_per_hour, photo) 
                  VALUES ('$parent_service_id', '$subservice_name', '$staff_name', '$price', '$photo')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Subservice added successfully!'); window.location.href = 'service.php';</script>";
        } else {
            echo "<script>alert('Error adding subservice.');</script>";
        }
    } else {
        echo "<script>alert('Failed to upload photo.');</script>";
    }
}

// Delete Service
if (isset($_GET['delete_service'])) {
    $service_id = mysqli_real_escape_string($conn, $_GET['delete_service']);
    $check_query = "SELECT id FROM services WHERE id = '$service_id'";
    if (mysqli_num_rows(mysqli_query($conn, $check_query)) > 0) {
        $query = "DELETE FROM services WHERE id = '$service_id'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Service deleted successfully!'); window.location.href = 'service.php';</script>";
        } else {
            echo "<script>alert('Error deleting service.');</script>";
        }
    } else {
        echo "<script>alert('Service not found.');</script>";
    }
}

// Delete Subservice
if (isset($_GET['delete_subservice'])) {
    $subservice_id = mysqli_real_escape_string($conn, $_GET['delete_subservice']);
    $check_query = "SELECT id FROM subservices WHERE id = '$subservice_id'";
    if (mysqli_num_rows(mysqli_query($conn, $check_query)) > 0) {
        $query = "DELETE FROM subservices WHERE id = '$subservice_id'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Subservice deleted successfully!'); window.location.href = 'service.php';</script>";
        } else {
            echo "<script>alert('Error deleting subservice.');</script>";
        }
    } else {
        echo "<script>alert('Subservice not found.');</script>";
    }
}

// Fetch services for autocomplete
$services_query = "SELECT id, category FROM services";
$services_result = mysqli_query($conn, $services_query);
$services = [];
while ($service = mysqli_fetch_assoc($services_result)) {
    $services[] = $service;
}

// Fetch services and subservices for display with search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$services_list_query = "
    SELECT s.id, s.category AS name, s.description, s.photo, 'service' AS type, NULL AS parent_name, NULL AS staff_name, NULL AS price 
    FROM services s 
    WHERE s.category LIKE '%$search%' 
    UNION 
    SELECT ss.id, ss.subservice_name AS name, ss.staff_name AS description, ss.photo, 'subservice' AS type, s.category AS parent_name, ss.staff_name, ss.price_per_hour AS price 
    FROM subservices ss 
    JOIN services s ON ss.parent_service_id = s.id 
    WHERE ss.subservice_name LIKE '%$search%' 
    ORDER BY name";
$services_list_result = mysqli_query($conn, $services_list_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Services | Easy Living</title>
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
            backdrop-filter: blur(12px);
            padding: 15px 30px;
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
            position: relative;
            overflow: hidden;
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

        .services-section {
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

        .services-section h2 {
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

        .service-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .service-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .service-card img {
            max-width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #34495e;
        }

        .service-card h3 {
            color: #e8b923;
            font-size: 18px;
            font-weight: 500;
        }

        .service-card p {
            color: #bdc3c7;
            font-size: 14px;
            margin: 5px 0;
        }

        .service-card .actions {
            display: flex;
            justify-content: flex-end;
            margin-top: auto;
        }

        .service-card .actions a {
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .service-card .actions .delete {
            background: #e74c3c;
            color: #fff;
        }

        .service-card .actions .delete:hover {
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

        .modal-content input, .modal-content select, .modal-content textarea {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #34495e;
            color: #ecf0f1;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .modal-content input:focus, .modal-content select:focus, .modal-content textarea:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(26, 188, 156, 0.5);
            background: #3e5c76;
        }

        .modal-content textarea {
            resize: vertical;
            min-height: 100px;
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
            background: linear-gradient(to right,rgb(223, 87, 28),rgb(226, 106, 50));
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
            background: linear-gradient(to right,rgb(231, 106, 53),rgb(219, 116, 47));
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

        .autocomplete-container {
            position: relative;
        }

        .autocomplete-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #34495e;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .autocomplete-suggestions div {
            padding: 12px;
            color: #ecf0f1;
            cursor: pointer;
            transition: background 0.3s;
        }

        .autocomplete-suggestions div:hover {
            background: #3e5c76;
        }

        .price-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .price-container span {
            color: #ecf0f1;
            font-size: 14px;
            font-weight: 500;
        }

        .price-container input {
            width: 150px; /* Reduced size for consistency */
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="back-to-dashboard">
            <a href="admin/dash.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <div class="nav-links">
            <a href="#" onclick="openModal('addServiceModal')"><i class="fas fa-plus"></i> Add Service</a>
            <a href="#" onclick="openModal('addSubserviceModal')"><i class="fas fa-plus-circle"></i> Add Subservice</a>
        </div>
    </nav>

    <div class="container">
        <div class="services-section">
            <h2>Manage Services</h2>
            <div class="search-bar">
                <form action="service.php" method="GET">
                    <input type="text" name="search" placeholder="Search services or subservices..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="service-list">
                <?php while ($item = mysqli_fetch_assoc($services_list_result)) : ?>
                    <div class="service-card">
                        <img src="../Uploads/services/<?php echo htmlspecialchars($item['photo']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <?php if ($item['type'] === 'service') : ?>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php else : ?>
                            <p><strong>Parent Service:</strong> <?php echo htmlspecialchars($item['parent_name']); ?></p>
                            <p><strong>Staff:</strong> <?php echo htmlspecialchars($item['staff_name']); ?></p>
                            <p><strong>Price:</strong> Rs <?php echo number_format($item['price'], 2); ?></p>
                        <?php endif; ?>
                        <div class="actions">
                            <a href="service.php?delete_<?php echo $item['type']; ?>=<?php echo $item['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($item['name']); ?>?')">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addServiceModal')">×</span>
            <h3>Add New Service</h3>
            <form action="service.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_service" value="1">
                <div>
                    <label for="category">Service Category</label>
                    <input type="text" name="category" id="category" placeholder="Enter service category" required>
                </div>
                <div>
                    <label for="description">Service Description</label>
                    <textarea name="description" id="description" placeholder="Describe the service" required></textarea>
                </div>
                <div class="file-upload-container">
                    <label for="photo" class="file-upload-label">Upload Service Photo</label>
                    <input type="file" name="photo" id="photo" accept="image/*" onchange="previewImage(this, 'preview-img-service')" required style="display: none;">
                    <div class="file-preview">
                        <img id="preview-img-service" src="" alt="Selected Image Preview" style="display: none;">
                    </div>
                </div>
                <button type="submit">Add Service</button>
            </form>
        </div>
    </div>

    <!-- Subservice Form-->
    <div id="addSubserviceModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addSubserviceModal')">×</span>
            <h3>Add New Subservice</h3>
            <form action="service.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_subservice" value="1">
                <input type="hidden" name="parent_service_id" id="parent_service_id">
                <div class="autocomplete-container">
                    <label for="parent_service">Parent Service</label>
                    <input type="text" id="parent_service" placeholder="Enter parent service" required oninput="showSuggestions(this.value)">
                    <div class="autocomplete-suggestions" id="suggestions"></div>
                </div>
                <div>
                    <label for="subservice_name">Subservice Name</label>
                    <input type="text" name="subservice_name" id="subservice_name" placeholder="Enter subservice name" required>
                </div>
                <div>
                    <label for="staff_name">Staff Name</label>
                    <input type="text" name="staff_name" id="staff_name" placeholder="Enter staff name" required>
                </div>
                <div class="price-container">
                    <label for="price">Price Rs</label>
                   
                    <input type="text" name="price" id="price" placeholder="Enter amount" pattern="[0-9]+(\.[0-9]{1,2})?" required>
                </div>
                <div class="file-upload-container">
                    <label for="photo_sub" class="file-upload-label">Upload Subservice Photo</label>
                    <input type="file" name="photo" id="photo_sub" accept="image/*" onchange="previewImage(this, 'preview-img-sub')" required style="display: none;">
                    <div class="file-preview">
                        <img id="preview-img-sub" src="" alt="Selected Image Preview" style="display: none;">
                    </div>
                </div>
                <button type="submit">Add Subservice</button>
            </form>
        </div>
    </div>

    <script>
        const services = <?php echo json_encode($services); ?>;

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'addServiceModal') {
                document.getElementById('preview-img-service').style.display = 'none';
                document.getElementById('photo').value = '';
            } else if (modalId === 'addSubserviceModal') {
                document.getElementById('preview-img-sub').style.display = 'none';
                document.getElementById('photo_sub').value = '';
                document.getElementById('parent_service').value = '';
                document.getElementById('parent_service_id').value = '';
                document.getElementById('suggestions').style.display = 'none';
            }
        }

        function previewImage(input, imgId) {
            const previewImg = document.getElementById(imgId);
            const file = input.files[0];
            const reader = new FileReader();

            reader.onloadend = function () {
                previewImg.src = reader.result;
                previewImg.style.display = 'block';
            };

            if (file) {
                reader.readAsDataURL(file);
            } else {
                previewImg.style.display = 'none';
            }
        }

        function showSuggestions(value) {
            const suggestions = document.getElementById('suggestions');
            suggestions.innerHTML = '';
            if (value.length === 0) {
                suggestions.style.display = 'none';
                return;
            }

            const filteredServices = services.filter(service => 
                service.category.toLowerCase().includes(value.toLowerCase())
            );

            if (filteredServices.length === 0) {
                suggestions.style.display = 'none';
                return;
            }

            filteredServices.forEach(service => {
                const div = document.createElement('div');
                div.textContent = service.category;
                div.onclick = function() {
                    document.getElementById('parent_service').value = service.category;
                    document.getElementById('parent_service_id').value = service.id;
                    suggestions.style.display = 'none';
                };
                suggestions.appendChild(div);
            });

            suggestions.style.display = 'block';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.getElementById('preview-img-service').style.display = 'none';
                document.getElementById('photo').value = '';
                document.getElementById('preview-img-sub').style.display = 'none';
                document.getElementById('photo_sub').value = '';
                document.getElementById('parent_service').value = '';
                document.getElementById('parent_service_id').value = '';
                document.getElementById('suggestions').style.display = 'none';
            }
        };
    </script>
</body>
</html>