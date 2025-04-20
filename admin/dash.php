<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Easy Living</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .dashboard {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 1000px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideUp 1s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .dashboard h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }

        .nav-buttons a {
            text-decoration: none;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            padding: 20px 35px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 500;
            min-width: 200px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-buttons a i {
            margin-right: 10px;
        }

        .nav-buttons a::after {
            content: "";
            position: absolute;
            width: 100%;
            height: 0%;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.2);
            transition: 0.3s;
            z-index: 0;
        }

        .nav-buttons a:hover::after {
            height: 100%;
        }

        .nav-buttons a:hover {
            transform: scale(1.05);
        }

        .logout {
            text-align: center;
            margin-top: 40px;
        }

        .logout a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: 0.2s;
        }

        .logout a:hover {
            color: #d63031;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome, Admin</h1>
        <div class="nav-buttons">
            <a href="booking.php"><i class="fas fa-calendar-check"></i> View Bookings</a>
            <a href="services.php"><i class="fas fa-tools"></i> Manage Services</a>
            <a href="staff.php"><i class="fas fa-user-cog"></i> Manage Staff</a>
        </div>
        <div class="logout">
            <br><br>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</body>
</html>
