<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon_db";  // Use the correct database name

// Create connection
$con = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['verify'])) {
    $enteredOTP = $_POST['otp'];

    // Check if entered OTP matches the generated OTP
    if (isset($_SESSION['otp']) && $_SESSION['otp'] == $enteredOTP) {
        // OTP verified successfully, proceed with registration
        $registrationData = $_SESSION['registration_data'];
        $username = mysqli_real_escape_string($con, $registrationData['username']);
        $email = mysqli_real_escape_string($con, $registrationData['email']);
        $fullname = mysqli_real_escape_string($con, $registrationData['fullname']);
        $password = mysqli_real_escape_string($con, $registrationData['password']);

        // Insert user into the database
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $query = "INSERT INTO registered_users (full_name, username, email, password) VALUES ('$fullname', '$username', '$email', '$passwordHash')";

        if (mysqli_query($con, $query)) {
            // Clear OTP and registration data from session
            unset($_SESSION['otp']);
            unset($_SESSION['registration_data']);
            // Redirect user to registration success page
            header("Location: registration_success.php");
            exit();
        } else {
            $error = "Failed to register user. Please try again.";
        }
    } else {
        // Incorrect OTP entered, display error message
        $error = "Incorrect OTP. Please try again.";
    }
}

// Close the database connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }
        h2 {
            color: #333;
            text-align: center;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 3px;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        p.error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>OTP Verification</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
        <label>Enter OTP sent to your email:</label><br>
        <input type="text" name="otp" required><br><br>
        <input type="submit" name="verify" value="Verify OTP">
    </form>
</body>
</html>
