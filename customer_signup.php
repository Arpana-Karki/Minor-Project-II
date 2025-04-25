<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $landmark = mysqli_real_escape_string($conn, $_POST['landmark']);
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM customers WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already registered!";
        } else {
            $verification_code = rand(100000, 999999);

            $query = "INSERT INTO customers (name, email, mobile, address, landmark, password, verification_code) 
                      VALUES ('$name', '$email', '$mobile', '$address', '$landmark', '$password', '$verification_code')";
            $inserted = mysqli_query($conn, $query);

            if ($inserted) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'sadibanstola@gmail.com';
                    $mail->Password = 'tmph adzq zhjq rqnw'; // App password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('sadibanstola@gmail.com', 'Easy Living');
                    $mail->addAddress($email, $name);
                    $mail->isHTML(true);
                    $mail->Subject = 'Email Verification Code';
                    $mail->Body = "<p>Dear $name,</p><p>Your Easy Living verification code is: <strong>$verification_code</strong></p>";

                    $mail->send();
                    session_start();
                    $_SESSION['verify_email'] = $email;
                    header("Location: verify_code.php");
                    exit();
                } catch (Exception $e) {
                    $error = "Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Error creating account!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Signup | Easy Living</title>
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
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 400px;
            animation: slideDown 1s ease;
        }
        @keyframes slideDown {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .login-box h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        .input-group input {
            width: 100%;
            padding: 12px 45px;
            border: 1px solid #ccc;
            border-radius: 30px;
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
        <h2>Customer Sign Up</h2>

        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="name" placeholder="Full Name" required>
        </div>

        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email Address" required>
        </div>

        <div class="input-group">
            <i class="fas fa-phone"></i>
            <input type="text" name="mobile" placeholder="Mobile Number" required>
        </div>

        <div class="input-group">
            <i class="fas fa-location-dot"></i>
            <input type="text" name="address" placeholder="Address" required>
        </div>

        <div class="input-group">
            <i class="fas fa-map-marker-alt"></i>
            <input type="text" name="landmark" placeholder="Landmark (Optional)">
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>

        <button type="submit" class="btn-login">Sign Up</button>

        <div class="extra">
            Already have an account? <a href="customer_login.php">Login here</a>
        </div>

        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
    </form>
</body>
</html>
