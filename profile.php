<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['customer_email'])) {
    header("Location: customer_login.php");
    exit();
}

$email = $_SESSION['customer_email'];
$query = "SELECT * FROM customers WHERE email='$email'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile | Easy Living</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .profile-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: slideDown 1s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .profile-icon {
            font-size: 90px;
            color: #007B83;
            margin-bottom: 25px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .profile-heading {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .profile-detail {
            display: flex;
            align-items: center;
            text-align: left;
            margin: 15px 0;
            font-size: 16px;
            color: #333;
            padding: 12px;
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .profile-detail:hover {
            background: #e0f7fa;
            transform: translateX(5px);
        }

        .profile-detail i {
            margin-right: 15px;
            color: #007B83;
            font-size: 18px;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .logout-btn, .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn {
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            color: white;
        }

        .logout-btn:hover {
            background: linear-gradient(to right, #ACB6E5, #74ebd5);
            transform: translateY(-2px);
        }

        .back-btn {
            background: linear-gradient(to right, #6c757d, #adb5bd);
            color: white;
        }

        .back-btn:hover {
            background: linear-gradient(to right, #adb5bd, #6c757d);
            transform: translateY(-2px);
        }

        .logout-btn i, .back-btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-icon">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="profile-heading">Welcome, <?php echo htmlspecialchars($row['name']); ?></div>
        
        <div class="profile-detail">
            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?>
        </div>
        <div class="profile-detail">
            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['mobile'] ?? 'Not provided'); ?>
        </div>
        <div class="profile-detail">
            <i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($row['address'] ?? 'Not provided'); ?>
        </div>
        <div class="profile-detail">
            <i class="fas fa-city"></i> <?php echo htmlspecialchars($row['landmark'] ?? 'Not provided'); ?>
        </div>

        <div class="button-container">
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="first.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</body>
</html>