<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $emailUsername = $_POST['email_username'];
    $password = $_POST['password'];
    $userType = $_POST['user_type'];

    $query = "SELECT * FROM `registered_users` WHERE `username`='$emailUsername' OR `email`='$emailUsername'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email']; // Store the user's email in the session

            // Check if the logged-in user is an admin
            if ($userType == 'admin' && $_SESSION['email'] == 'salon@gmail.com') {
                // Admin login
                header("location: ad_admin.php");
                exit();
            } elseif ($userType != 'admin') {
                // Regular user login
                header("location: home.php");
                exit();
            } else {
                // Not an admin
                echo "<script>
                        alert('You are not an admin.');
                        window.location.href='login.php';
                      </script>";
                exit();
            }
        } else {
            // Incorrect password
            echo "<script>
                    alert('Incorrect password');
                    window.location.href='login.php';
                  </script>";
            exit();
        }
    } else {
        // Invalid username or email
        echo "<script>
                alert('Invalid username or email');
                window.location.href='login.php';
              </script>";
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalonSpear - Style & Confidence</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .main {
            background-color: #fff;
            width: 100%;
            max-width: 450px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px;
        }
        
        .logo-container {
            text-align: center;
            margin: 20px 0 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .brand-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .brand-name {
            font-size: 32px;
            color: #333;
            font-weight: 600;
            margin-left: 10px;
        }
        
        /* Scissor Animation */
        .scissors-container {
            width: 60px;
            height: 60px;
            position: relative;
        }
        
        .scissor {
            position: relative;
            width: 100%;
            height: 100%;
        }
        
        .scissor-top {
            position: absolute;
            width: 40px;
            height: 10px;
            background-color: #1f2937;
            border-radius: 10px;
            top: 15px;
            left: 20px;
            transform: rotate(45deg);
            transform-origin: 0 5px;
        }
        
        .scissor-bottom {
            position: absolute;
            width: 40px;
            height: 10px;
            background-color: #1f2937;
            border-radius: 50px;
            top: 35px;
            left: 20px;
            transform: rotate(-45deg);
            transform-origin: 0 5px;
        }
        
        .scissor-handle-top {
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #1f2937;
            top: 5px;
            left: 5px;
        }
        
        .scissor-handle-bottom {
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #1f2937;
            bottom: 5px;
            left: 5px;
        }
        
        .main .tagline {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .form-group {
            margin: 0 30px 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .input-field {
            width: 100%;
            height: 48px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0 15px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .input-field:focus {
            border-color: #ff4757;
        }
        
        .submit-field {
            width: calc(100% - 60px);
            margin: 10px 30px 20px;
            height: 50px;
            background-color: #ff4757;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .submit-field:hover {
            background-color: #ff5e69;
        }
        
        .login-link, .register-link {
            text-align: center;
            margin-bottom: 30px;
            color: #666;
        }
        
        .signin, .signup {
            color: #ff4757;
            text-decoration: none;
            font-weight: 500;
            margin-left: 5px;
        }
        
        .signin:hover, .signup:hover {
            text-decoration: underline;
        }
        
        /* Hide the original tabs */
        .login-tab, .register-tab {
            display: none !important;
        }
        
        .login-box, .register-box {
            padding: 10px 0 20px;
        }
        
        /* User type selector */
        #user_type, select {
            height: 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0 10px;
            font-size: 15px;
            width: 100%;
            color: #333;
        }
        
        /* Hidden elements */
        .template, nav, .aboutus, footer, .home, .logout, .cont, ul, li, button {
            display: none;
        }
        
        /* Animation Keyframes */
        @keyframes cut {
            0% {
                transform: rotate(45deg);
            }
            50% {
                transform: rotate(30deg);
            }
            100% {
                transform: rotate(45deg);
            }
        }
        
        @keyframes cutReverse {
            0% {
                transform: rotate(-45deg);
            }
            50% {
                transform: rotate(-30deg);
            }
            100% {
                transform: rotate(-45deg);
            }
        }
    </style>
    <script src="https://kit.fontawesome.com/b41a456f5c.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="main">
        <div class="main-box">
            <!-- Login Form -->
            <div class="login-box">
                <div class="logo-container">
                    <div class="brand-container">
                        <div class="scissors-container">
                            <div class="scissor">
                                <div class="scissor-handle-top"></div>
                                <div class="scissor-handle-bottom"></div>
                                <div class="scissor-top"></div>
                                <div class="scissor-bottom"></div>
                            </div>
                        </div>
                        <h1 class="brand-name">Salonspear</h1>
                    </div>
                </div>
                <p class="tagline">Style & Confidence</p>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="user_type">User Type</label>
                        <select id="user_type" name="user_type">
                            <option value="customer" selected>Customer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email_username">Email/Username</label>
                        <input class="input-field" name="email_username" id="email_username" type="text" placeholder="Enter your email or username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input class="input-field" name="password" id="password" type="password" placeholder="Enter your password" required>
                    </div>
                    <input class="submit-field" name="login" type="submit" value="Login">
                    <p class="login-link">Don't have an account? <a href="#" class="signup" onclick="toggleTab('admin')">Sign up</a></p>
                </form>
            </div>

            <!-- Registration Form -->
            <div class="register-box" hidden>
                <div class="logo-container">
                    <div class="brand-container">
                        <div class="scissors-container">
                            <div class="scissor">
                                <div class="scissor-handle-top"></div>
                                <div class="scissor-handle-bottom"></div>
                                <div class="scissor-top"></div>
                                <div class="scissor-bottom"></div>
                            </div>
                        </div>
                        <h1 class="brand-name">Salonspear</h1>
                    </div>
                </div>
                <p class="tagline">Style & Confidence</p>
                <form action="login_registration.php" method="POST">
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input class="input-field" name="fullname" id="fullname" type="text" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input class="input-field" name="phone" id="phone" type="tel" placeholder="Enter your phone number">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="input-field" name="email" id="email" type="email" placeholder="Enter your email">
                    </div>
                    <div class="form-group">
                        <label for="password_reg">Password</label>
                        <input class="input-field" name="password" id="password_reg" type="password" placeholder="Create a password" required>
                    </div>
                    <input class="submit-field" name="register" type="submit" value="Sign Up">
                    <p class="register-link">Already have an account? <a href="#" class="signin" onclick="toggleTab('customer')">Login</a></p>
                </form>
            </div>

            <!-- Hidden toggle tabs from original code -->
            <div class="login-tab active" onclick="toggleTab('customer')">Login</div>
            <div class="register-tab" onclick="toggleTab('admin')">register</div>
        </div>
    </div>

    <script>
        function toggleTab(userType) {
            if (userType === 'customer') {
                document.querySelector('.login-box').style.display = 'block';
                document.querySelector('.register-box').style.display = 'none';
                document.querySelector('.login-tab').classList.add('active');
                document.querySelector('.register-tab').classList.remove('active');
                if (document.querySelector('#user_type')) {
                    document.querySelector('#user_type').value = 'customer';
                }
            } else {
                document.querySelector('.login-box').style.display = 'none';
                document.querySelector('.register-box').style.display = 'block';
                document.querySelector('.login-tab').classList.remove('active');
                document.querySelector('.register-tab').classList.add('active');
            }
        }

        // Animate scissors
        document.addEventListener('DOMContentLoaded', function() {
            const scissorTops = document.querySelectorAll('.scissor-top');
            const scissorBottoms = document.querySelectorAll('.scissor-bottom');
            
            scissorTops.forEach(function(top) {
                top.style.animation = 'cut 1.5s infinite';
            });
            
            scissorBottoms.forEach(function(bottom) {
                bottom.style.animation = 'cutReverse 1.5s infinite';
            });
        });
    </script>
</body>

</html>