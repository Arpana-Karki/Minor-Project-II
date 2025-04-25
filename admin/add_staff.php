<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

include('../db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = $_POST['staff_id'];
    $staff_name = $_POST['staff_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $subservices = isset($_POST['subservices']) ? $_POST['subservices'] : [];
    
    // Handle photo upload
    $photo_path = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = '../uploads/staff_photos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $photo_path = $upload_dir . uniqid() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    // Insert staff into database
    $query = "INSERT INTO staff (staff_id, staff_name, email, phone_number, photo_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssss', $staff_id, $staff_name, $email, $phone_number, $photo_path);
    $success = mysqli_stmt_execute($stmt);

    // Insert staff-subservices relationships
    if ($success && !empty($subservices)) {
        foreach ($subservices as $subservice_id) {
            $sub_query = "INSERT INTO staff_subservices (staff_id, subservice_id) VALUES (?, ?)";
            $sub_stmt = mysqli_prepare($conn, $sub_query);
            mysqli_stmt_bind_param($sub_stmt, 'si', $staff_id, $subservice_id);
            mysqli_stmt_execute($sub_stmt);
        }
    }

    if ($success) {
        header("Location: staff.php");
        exit();
    } else {
        $error = "Error adding staff. Please try again.";
    }
}

// Fetch subservices for dropdown
$subservices_query = "SELECT * FROM subservices";
$subservices_result = mysqli_query($conn, $subservices_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Staff | Easy Living</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a1a1a, #2c3e50);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-container {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        h2 {
            text-align: center;
            color: #ecf0f1;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            color: #ecf0f1;
            margin-bottom: 5px;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #2c3e50;
            color: #ecf0f1;
        }

        .input-group select[multiple] {
            height: 100px;
        }

        .input-group input[type="file"] {
            padding: 5px;
        }

        .photo-preview {
            margin-top: 10px;
            max-width: 100%;
            max-height: 200px;
            display: none;
            border-radius: 10px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #1abc9c, #16a085);
            border: none;
            color: #fff;
            border-radius: 20px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: linear-gradient(to right, #16a085, #1abc9c);
        }

        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Staff</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="staff_id">Staff ID</label>
                <input type="text" id="staff_id" name="staff_id" required>
            </div>
            <div class="input-group">
                <label for="staff_name">Staff Name</label>
                <input type="text" id="staff_name" name="staff_name" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" required>
            </div>
            <div class="input-group">
                <label for="subservices">Service Categories (Hold Ctrl to select multiple, max 4)</label>
                <select id="subservices" name="subservices[]" multiple size="4">
                    <?php while ($row = mysqli_fetch_assoc($subservices_result)) { ?>
                        <option value="<?php echo $row['subservice_id']; ?>">
                            <?php echo htmlspecialchars($row['subservice_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="input-group">
                <label for="photo">Upload Photo</label>
                <input type="file" id="photo" name="photo" accept="image/*" required>
                <img id="photoPreview" class="photo-preview" src="" alt="Photo Preview">
            </div>
            <button type="submit" class="submit-btn">Add Staff</button>
        </form>
    </div>

    <script>
        document.getElementById('photo').addEventListener('change', function(event) {
            const preview = document.getElementById('photoPreview');
            const file = event.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });

        document.getElementById('subservices').addEventListener('change', function() {
            const selectedOptions = Array.from(this.selectedOptions);
            if (selectedOptions.length > 4) {
                alert('You can select up to 4 subservices only.');
                selectedOptions.slice(4).forEach(option => option.selected = false);
            }
        });
    </script>
</body>
</html>