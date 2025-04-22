<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['verify_email'])) {
    header("Location: signup.php");
    exit();
}

$email = $_SESSION['verify_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['code'];

    $query = "SELECT verification_code FROM customers WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    if ($data && $entered_code == $data['verification_code']) {
        mysqli_query($conn, "UPDATE customers SET is_verified=1 WHERE email='$email'");
        unset($_SESSION['verify_email']);
        header("Location: customer_login.php?verified=1");
        exit();
    } else {
        $error = "Invalid verification code!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Code | Easy Living</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0; padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .verify-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 400px;
            animation: slideDown 1s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .verify-box h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
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
            font-size: 15px;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .btn-verify {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            border: none;
            color: white;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-verify:hover {
            background: linear-gradient(to right, #ACB6E5, #74ebd5);
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 15px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <form class="verify-box" method="POST" action="">
        <h2>Email Verification</h2>

        <div class="input-group">
            <i class="fas fa-key"></i>
            <input type="text" name="code" placeholder="Enter 6-digit code" required>
        </div>

        <button type="submit" class="btn-verify">Verify</button>

        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
    </form>
</body>
</html>
