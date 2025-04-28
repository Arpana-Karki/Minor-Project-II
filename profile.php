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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f1f5f9;
            min-height: 100vh;
            padding: 20px;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
            font-size: 1rem;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #4f46e5;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        .action-btn {
            background: linear-gradient(to right, #4f46e5, #7c3aed);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        .action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .animate-scale-in {
            animation: scaleIn 0.5s ease-out forwards;
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .menu-toggle {
            display: none;
            cursor: pointer;
        }

        /* Profile Styling */
        .profile-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: slideDown 1s ease;
            margin: 40px auto;
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
            justify-content: center;
            margin-top: 30px;
        }

        .logout-btn {
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
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            color: white;
        }

        .logout-btn:hover {
            background: linear-gradient(to right, #ACB6E5, #74ebd5);
            transform: translateY(-2px);
        }

        .logout-btn i {
            margin-right: 8px;
        }

        /* Responsive Design for Navbar */
        @media (max-width: 768px) {
            .navbar ul {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #f8f9fa;
                padding: 1rem;
            }

            .navbar ul.active {
                display: flex;
            }

            .menu-toggle {
                display: block;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-indigo-600 animate-scale-in">EasyLiving</a>
            <div class="flex items-center space-x-6">
                <ul class="flex space-x-6 text-gray-700">
                    <li><a href="index.php" class="nav-link hover:text-indigo-600">Home</a></li>
                    <li><a href="about.php" class="nav-link hover:text-indigo-600">About</a></li>
                    <li><a href="./customer/package.php" class="nav-link hover:text-indigo-600">Packages</a></li>
                    <li><a href="./customer/subservice.php" class="nav-link hover:text-indigo-600">Services</a></li>
                </ul>
                <!-- Wishlist -->
                <a href="./customer/favorites.php" class="action-btn">
                    <i class="fas fa-heart mr-2"></i> Favorites
                </a>
                <!-- My Bookings -->
                <a href="./customer/my_bookings.php" class="action-btn">
                    <i class="fas fa-calendar-check mr-2"></i> My Bookings
                </a>
                <!-- My Profile -->
                <a href="profile.php" class="action-btn">
                    <i class="fas fa-user-circle mr-2"></i> My Profile
                </a>
                <div class="menu-toggle text-gray-700"><i class="fas fa-bars"></i></div>
            </div>
        </div>
    </nav>

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
            <a href="first.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <script>
        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.navbar ul');
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }
    </script>
</body>
</html>