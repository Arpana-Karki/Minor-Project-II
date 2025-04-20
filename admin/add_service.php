<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}
include('../db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from the form
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $sub_service = mysqli_real_escape_string($conn, $_POST['sub_service']);
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $price_per_hour = mysqli_real_escape_string($conn, $_POST['price_per_hour']);

    // Handle file upload
    $photo = $_FILES['photo']['name'];
    $photo_temp = $_FILES['photo']['tmp_name'];
    $photo_dir = '../uploads/services/' . $photo;

    if (move_uploaded_file($photo_temp, $photo_dir)) {
        // Insert into database
        $query = "INSERT INTO services (category, sub_service, staff_name, price_per_hour, photo) 
                  VALUES ('$category', '$sub_service', '$staff_name', '$price_per_hour', '$photo')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Service added successfully!'); window.location.href = 'services.php';</script>";
        } else {
            echo "<script>alert('Error adding service.');</script>";
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Service</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f7f9fc;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 900px;
        margin: 50px auto;
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #2f3542;
        font-size: 30px;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-size: 16px;
        color: #555;
        margin-bottom: 10px;
        display: block;
    }

    .form-group input, .form-group select, .form-group textarea {
        width: 100%;
        padding: 14px;
        font-size: 16px;
        border-radius: 8px;
        border: 1px solid #ddd;
        margin-top: 8px;
        box-sizing: border-box;
        transition: all 0.3s ease-in-out;
    }

    .form-group input[type="file"] {
        font-size: 16px;
    }

    .form-group input[type="text"]:focus, .form-group input[type="number"]:focus {
        border-color: #1abc9c;
        box-shadow: 0 0 8px rgba(26, 188, 156, 0.4);
    }

    .submit-btn {
        background-color: #1abc9c;
        color: white;
        padding: 14px 25px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer;
        transition: 0.3s;
        width: 100%;
        display: block;
        margin-top: 15px;
        text-align: center;
        box-shadow: 0 6px 12px rgba(26, 188, 156, 0.2);
    }

    .submit-btn:hover {
        background-color: #16a085;
        box-shadow: 0 8px 16px rgba(26, 188, 156, 0.3);
    }

    .file-upload-container {
        margin-top: 20px;
        position: relative;
        text-align: center;
    }

    .file-upload-container input[type="file"] {
        display: none;
    }

    .file-upload-label {
        background-color: #1abc9c;
        color: white;
        padding: 15px 20px;
        cursor: pointer;
        border-radius: 6px;
        font-size: 16px;
        transition: background-color 0.3s ease;
        box-shadow: 0 4px 8px rgba(26, 188, 156, 0.3);
    }

    .file-upload-label:hover {
        background-color: #16a085;
    }

    .file-preview {
        margin-top: 15px;
        max-width: 100%;
        height: auto;
        display: none;
    }

    .file-preview img {
        max-width: 100%;
        border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Add New Service</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" name="category" id="category" placeholder="Enter service category (e.g., Cleaning)" required>
        </div>

        <div class="form-group">
            <label for="sub_service">Sub-Service:</label>
            <input type="text" name="sub_service" id="sub_service" placeholder="Enter sub-service (e.g., Room Cleaning)" required>
        </div>

        <div class="form-group">
            <label for="staff_name">Staff Name:</label>
            <input type="text" name="staff_name" id="staff_name" placeholder="Enter staff name" required>
        </div>

        <div class="form-group">
            <label for="price_per_hour">Price Per Hour:</label>
            <input type="number" name="price_per_hour" id="price_per_hour" placeholder="Enter amount (e.g., 500)" required>
        </div>

        <div class="form-group file-upload-container">
            <label for="photo" class="file-upload-label">Choose Service Photo</label>
            <input type="file" name="photo" id="photo" accept="image/*" onchange="previewImage()" required>
            <div class="file-preview" id="file-preview">
                <img id="preview-img" src="" alt="Selected Image Preview">
            </div>
        </div>

        <button type="submit" class="submit-btn">Add Service</button>
    </form>
  </div>

  <script>
    function previewImage() {
        const fileInput = document.getElementById('photo');
        const preview = document.getElementById('file-preview');
        const previewImg = document.getElementById('preview-img');

        const file = fileInput.files[0];
        const reader = new FileReader();

        reader.onloadend = function () {
            preview.style.display = 'block';
            previewImg.src = reader.result;
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    }
  </script>
</body>
</html>
