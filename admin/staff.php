<?php
// Include database connection file
include '../db_connection.php';

// Fetch staff data
$query = "SELECT * FROM staff";
$result = mysqli_query($conn, $query);

// Handle search functionality
if (isset($_POST['search'])) {
    $searchTerm = $_POST['searchTerm'];
    $query = "SELECT * FROM staff WHERE name LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%'";
    $result = mysqli_query($conn, $query);
}

// Handle staff deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteQuery = "DELETE FROM staff WHERE id = $id";
    mysqli_query($conn, $deleteQuery);
    header("Location: staff.php"); // Redirect after delete
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f6;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-custom {
            background-color: #4CAF50;
            color: white;
            border-radius: 30px;
            padding: 10px 30px;
            font-size: 16px;
            text-transform: uppercase;
        }
        .btn-custom:hover {
            background-color: #45a049;
        }
        .table th, .table td {
            text-align: center;
        }
        .form-control, .input-group-text {
            border-radius: 30px;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .staff-photo {
            max-width: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1>Manage Staff</h1>
    
    <!-- Search Bar -->
    <form class="search-bar" method="POST">
        <div class="input-group">
            <input type="text" class="form-control" name="searchTerm" placeholder="Search by Name or Email">
            <div class="input-group-append">
                <button class="btn btn-custom" name="search" type="submit">Search</button>
            </div>
        </div>
    </form>

    <!-- Add Staff Button -->
    <a href="add_staff.php" class="btn btn-custom mb-3">Add New Staff</a>

    <!-- Staff Table -->
    <table class="table table-bordered">
        <thead class="thead-light">
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Category</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><img src="uploads/<?php echo $row['photo']; ?>" alt="Staff Photo" class="staff-photo"></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['category']; ?></td>
                <td>
                    <a href="edit_staff.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
