<?php
session_start();

// Database connection details
$servername = "localhost"; // or your database server IP
$username = "root";        // your database username
$password = "";            // your database password (default is empty for XAMPP)
$dbname = "easy_living";   // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, 3306);

// Check for database connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve user inputs
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = $_POST["role"]; // Getting role (admin/customer) from the form

    if ($role == 'admin') {
        // Admin login validation
        if ($email == 'easyliving@gmail.com') {
            // Check if admin exists in the database
            $sql = "SELECT id, full_name, password FROM users WHERE email = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id, $full_name, $hashed_password);

                // Check if user exists
                if ($stmt->num_rows > 0) {
                    $stmt->fetch();
                    
                    // Verify the password
                    if (password_verify($password, $hashed_password)) {
                        // Set session variables
                        $_SESSION["user_id"] = $id;
                        $_SESSION["full_name"] = $full_name;

                        // Redirect to admin home page
                        header("Location: ad_home.php");
                        exit();
                    } else {
                        // Incorrect password
                        $error = "Incorrect password!";
                    }
                } else {
                    // No user found with that email
                    $error = "No user found with that email!";
                }

                $stmt->close();  // Close the statement
            } else {
                // Handle SQL query preparation failure
                $error = "There was an error with the database query.";
            }
        } else {
            $error = "You are not an admin.";
        }
    } elseif ($role == 'customer') {
        // Customer login validation
        $sql = "SELECT id, full_name, password FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $full_name, $hashed_password);

            // Check if user exists
            if ($stmt->num_rows > 0) {
                $stmt->fetch();
                
                // Verify the password
                if (password_verify($password, $hashed_password)) {
                    // Set session variables
                    $_SESSION["user_id"] = $id;
                    $_SESSION["full_name"] = $full_name;

                    // Redirect to customer home page
                    header("Location: home.php");  // Redirect customer to home.php
                    exit();
                } else {
                    // Incorrect password
                    $error = "Incorrect password!";
                }
            } else {
                // No user found with that email
                $error = "No user found with that email!";
            }

            $stmt->close();  // Close the statement
        } else {
            // Handle SQL query preparation failure
            $error = "There was an error with the database query.";
        }
    }
}

$conn->close();  // Close the database connection
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
        }

        .container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #282929;
            color: #fff;
            padding: 10px 20px;
            height: 5rem;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-logo {
            font-size: 24px;
            color: #f3eeee;
            text-decoration: none;
        }

        .navbar-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .navbar-links li {
            margin: 0 15px;
        }

        .navbar-links a {
            color: #f2eeee;
            text-decoration: none;
            font-size: 18px;
            padding: 5px 10px;
        }

        .navbar-links a:hover {
            background-color: #555;
            border-radius: 5px;
        }

        /* Responsive Navbar */
        @media (max-width: 768px) {
            .navbar .container {
                flex-direction: column;
                align-items: center;
            }

            .navbar-links {
                flex-direction: column;
                align-items: center;
                width: 100%;
                display: none;
            }

            .navbar-links.active {
                display: flex;
            }

            .navbar-links li {
                margin: 10px 0;
            }

            .menu-toggle {
                display: block;
                cursor: pointer;
                font-size: 24px;
                color: #fff;
                background: none;
                border: none;
                position: absolute;
                right: 20px;
                top: 15px;
            }
        }

        @media (max-width: 480px) {
            .navbar-logo {
                font-size: 20px;
            }

            .menu-toggle {
                font-size: 20px;
            }
        }

        /* Hero Section */
        .hero {
            background: url('./image/background-scaled.jpg') no-repeat center center/cover;
            background-color: #b2d1f3;
            color: white;
            text-align: center;
            padding: 20px 0;
        }

        .hero-title {
            color: #040404;
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            color: #040404;
            font-size: 1.5em;
            margin-bottom: 30px;
        }

        .hero .btn {
            background-color: #007bff;
            color: #fff;
            padding: 12px 30px;
            text-decoration: none;
            font-size: 1.2em;
            border-radius: 5px;
        }

        .hero .btn:hover {
            background-color: #0056b3;
        }

        /* Form Styles */
        .form-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .form-box h2 {
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            background: #007bff;
            color: white;
        }

        /* Login Form */
        .login-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .login-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .login-form button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-form button:hover {
            background-color: #0056b3;
        }

        .login-form p {
            text-align: center;
            margin-top: 20px;
        }

        /* Payment Page Styling */
        .payment-container {
            text-align: center;
            margin-top: 50px;
        }

        .payment-container form {
            margin: 20px 0;
        }

        .payment-container input {
            margin: 10px;
            padding: 5px;
        }

        .payment-container button {
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
        }

        .payment-container button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-logo">EasyLiving</a>
            <ul class="navbar-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="services.php">Services</a></li>
                <?php if (isset($_SESSION["user_id"])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <h1 class="hero-title">Login</h1>
            <p class="hero-subtitle">Access your account</p>
        </div>
    </header>

    <section class="login-form">
        <div class="container">
            <div class="form-box">
                <h2>Login</h2>
                <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required />
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required />
                    </div>
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select name="role" id="role" required>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Login</button>
                </form>
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 EasyLiving. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
