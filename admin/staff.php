<?php
// Include DB connection
include '../db_connection.php';

// Handle form submission
if (isset($_POST['submit'])) {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $category = $_POST['category'];

    // File Upload
    $photoName = $_FILES['photo']['name'];
    $photoTmp  = $_FILES['photo']['tmp_name'];
    $uploadDir = "uploads";

    // Create uploads/ directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $photoPath = $uploadDir . basename($photoName);

    if (move_uploaded_file($photoTmp, $photoPath)) {
        // Save to DB
        $query = "INSERT INTO staff (name, email, phone, category, photo) 
                  VALUES ('$name', '$email', '$phone', '$category', '$photoName')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Staff added successfully'); window.location.href='staff.php';</script>";
        } else {
            echo "<script>alert('Failed to save data.');</script>";
        }
    } else {
        echo "<script>alert('Failed to upload photo.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Staff</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Roboto', sans-serif;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 60px;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 30px;
        }
        .btn-custom {
            background-color: #4CAF50;
            color: white;
            border-radius: 30px;
            padding: 10px 30px;
            text-transform: uppercase;
        }
        .btn-custom:hover {
            background-color: #45a049;
        }
        .form-control {
            border-radius: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add New Staff</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Staff Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter Name" required>
        </div>
        <div class="form-group">
            <label>Email ID</label>
            <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="Enter Phone" required>
        </div>
        <div class="form-group">
            <label>Service Category</label>
            <input type="text" name="category" class="form-control" placeholder="Enter Service Category" required>
        </div>
        <div class="form-group">
            <label>Upload Photo</label>
            <input type="file" name="photo" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-custom">Add Staff</button>
    </form>
</div>

</body>
</html>
