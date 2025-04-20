<?php
include 'db_connection.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = $_POST['staff_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $category = $_POST['category'];

    // Image upload
    $target_dir = "uploads/staff/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $photo_name = basename($_FILES["photo"]["name"]);
    $target_file = $target_dir . $photo_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO staff (staff_id, name, email, phone, category, photo) 
                VALUES ('$staff_id', '$name', '$email', '$phone', '$category', '$target_file')";
        if (mysqli_query($conn, $sql)) {
            $message = "Staff added successfully!";
        } else {
            $message = "Error adding staff: " . mysqli_error($conn);
        }
    } else {
        $message = "Failed to upload photo.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Staff</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 450px;
            margin: 60px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 35px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }

        input[type="text"], input[type="email"], input[type="file"], input[type="tel"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 6px;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .submit-btn {
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background: #4CAF50;
            color: #fff;
            border: none;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #45a049;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Add New Staff</h2>
    <?php if ($message): ?>
        <p class="message <?= strpos($message, 'Error') !== false || strpos($message, 'Failed') !== false ? 'error' : '' ?>">
            <?= $message ?>
        </p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Staff ID</label>
        <input type="text" name="staff_id" required>

        <label>Staff Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Phone Number</label>
        <input type="tel" name="phone" required>

        <label>Service Category</label>
        <input type="text" name="category" required>

        <label>Staff Photo</label>
        <input type="file" name="photo" accept="image/*" required>

        <button type="submit" class="submit-btn">Add Staff</button>
    </form>
</div>
</body>
</html>
