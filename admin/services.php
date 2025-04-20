<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}
include('../db_connection.php');

// Fetch all services
$services = mysqli_query($conn, "SELECT * FROM services ORDER BY created_at DESC");

// If searching, apply filter
$searchQuery = '';
if (isset($_POST['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_POST['search']);
    $searchQuery = " WHERE category LIKE '%$searchTerm%' OR sub_service LIKE '%$searchTerm%' OR staff_name LIKE '%$searchTerm%'";
    $services = mysqli_query($conn, "SELECT * FROM services $searchQuery ORDER BY created_at DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Services</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f7f9fc;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1200px;
        margin: 50px auto;
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
        animation: fadeIn 0.6s ease;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #2f3542;
    }

    .top-btns {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .add-btn {
        background-color: #1abc9c;
        color: #fff;
        padding: 12px 25px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
        box-shadow: 0 4px 8px rgba(26, 188, 156, 0.3);
    }

    .add-btn:hover {
        background-color: #16a085;
        box-shadow: 0 6px 12px rgba(26, 188, 156, 0.4);
    }

    .search-bar {
        padding: 8px 20px;
        border-radius: 6px;
        border: 1px solid #ddd;
        font-size: 14px;
        width: 300px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        padding: 14px;
        text-align: left;
        border-bottom: 1px solid #ccc;
        font-size: 14px;
    }

    th {
        background-color: #2d98da;
        color: white;
    }

    tr:hover {
        background-color: #f1f2f6;
    }

    img.service-img {
        height: 60px;
        width: 60px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #ccc;
    }

    .action-btn {
        padding: 6px 10px;
        font-size: 13px;
        margin-right: 5px;
        border-radius: 5px;
        color: white;
        border: none;
        cursor: pointer;
    }

    .edit {
        background-color: #1e90ff;
    }

    .delete {
        background-color: #e74c3c;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Manage Services</h2>
    <div class="top-btns">
        <a href="add_service.php" class="add-btn"><i class="fas fa-plus"></i> Add Service</a>
        <form method="POST">
            <input type="text" name="search" class="search-bar" placeholder="Search Services..." value="<?php echo isset($searchTerm) ? $searchTerm : ''; ?>">
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Category</th>
                <th>Sub-Service</th>
                <th>Staff</th>
                <th>Price (per hour)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($services)): ?>
                <tr>
                    <td><img src="../uploads/services/<?php echo $row['photo']; ?>" class="service-img"></td>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['sub_service']; ?></td>
                    <td><?php echo $row['staff_name']; ?></td>
                    <td>Rs. <?php echo number_format($row['price_per_hour'], 2); ?></td>
                    <td>
                        <a href="edit_service.php?id=<?php echo $row['id']; ?>"><button class="action-btn edit"><i class="fas fa-edit"></i></button></a>
                        <a href="delete_service.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure to delete this service?');"><button class="action-btn delete"><i class="fas fa-trash"></i></button></a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
  </div>
</body>
</html>
