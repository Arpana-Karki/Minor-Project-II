<?php
session_start();
include('db_connection.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = "SELECT * FROM customers WHERE email='$email' AND password='$password' AND is_verified=1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['customer_email'] = $email;
        header("Location: index.php"); //index
        exit();
    } else {
        $error = "Invalid Email, Password, or Account not verified!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Login | Easy Living</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
       
        * {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 400px;
            position: relative;
            animation: slideDown 1s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .login-box h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 45px;
            border: 1px solid #ccc;
            border-radius: 30px;
            transition: 0.3s ease;
        }

        .input-group input:focus {
            border-color: #74ebd5;
            outline: none;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            border: none;
            color: white;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-login:hover {
            background: linear-gradient(to right, #ACB6E5, #74ebd5);
        }

        .extra {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .extra a {
            color: #74ebd5;
            text-decoration: none;
        }

        .extra a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }
    </style>
</head>
<body>
    <form class="login-box" method="POST" action="">
        <h2>Customer Login</h2>

        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Enter Email" required>
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Enter Password" required>
        </div>

        <button type="submit" class="btn-login">Login</button>

        <div class="extra">
            Don't have an account? <a href="customer_signup.php">Sign up here</a>
        </div>

        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
    </form>
</body>
</html>
