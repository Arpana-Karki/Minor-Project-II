<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
$hostname = 'localhost'; 
$username = 'root'; 
$password = ''; 
$database = 'salon_db';

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to add a new service
function addService($name, $description, $image, $price) {
    global $conn;

    // Upload image
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($image["name"]);
    move_uploaded_file($image["tmp_name"], $targetFile);

    // Insert service details into the database
    $sql = "INSERT INTO services (name, description, image_path, is_available, price) VALUES (?, ?, ?, 1, ?)";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    // Bind parameters
    $stmt->bind_param("sssd", $name, $description, $targetFile, $price);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceName = $_POST["service_name"];
    $serviceDescription = $_POST["service_description"];
    $serviceImage = $_FILES["service_image"];
    $servicePrice = floatval($_POST["service_price"]);

    if (addService($serviceName, $serviceDescription, $serviceImage, $servicePrice)) {
        echo "Service added successfully.";
    } else {
        echo "Error adding service.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="scripts.js"></script>
    <style>
         *{
            margin: 0;  
        }
        main{
            background-image: url(back.jpg);
            background-repeat: no-repeat;
            background-size: cover;
            width: 100%;
            height: 100vh;
            margin-top: -20px
        }
        h1{
            color:#fff;
        }
        .home{
            font-weight: 900 !important;
        }
        .logout{
            text-decoration: none !important;
            color: #fff !important;
            background-color: #3200a0 !important;
            padding: 10px;
            border: 2px solid transparent;
            border-radius: 14px;
            top:90%;
            right:20px;
            position: fixed;
        }   
        .logout:hover{
            background-color: rgb(243, 134, 95) !important;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
        }          
        button{
            border: none;
            float: right;
            background: none;
        }
        nav{
            background: linear-gradient(to right,hsl(0, 0.00%, 100.00%),rgb(199, 202, 178),rgb(227, 234, 92));
            height: 80px;
            width: 100%;
        }
        .cont{
            color: white;
            font-size: 35px;
            line-height: 80px;
            padding: 0px;
            font-weight: bold;
            margin-left: 20px;
        }
        .cont:hover{
            color: coral;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
            text-shadow: 0 0 3px #FF0000;
        }
        ul{
            float: right;
            margin-right: 100px;
            margin-top: -10px; 
        }
        li{
            display: inline-block;
            line-height: 80px;
            margin: 0 5px;
            
        }
        li a{
            color: white;
            font-size: 16px;
            padding: 7px 13px;
            border-radius: 4px;
            text-transform: uppercase;
            border-bottom: 4px solid coral;
        }
        .active{
            text-decoration: none;
            background: transparent;
            transition: 0.5s;
        }
        .active:hover{
            background: coral;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
        }
        /* Form container styling */
        .container{
            
        }
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Form element styling */
        .form-field {
            margin-bottom: 15px;
        }

        .form-field label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-field input[type="text"],
        .form-field textarea,
        .form-field select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; 
        }

        .form-field input[type="file"] {
            border: none;
        }

        /* Button styling */
        .form-button {
            background-color: #3200a0;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-button:hover {
            background-color: #5434c8;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .form-container {
                width: 90%;
                margin: 20px auto;
            }
        }
        footer{
                background-color: #111;
                padding: 20px;
            }
            .footerContainer{
                width: 100%;
            }
            .socialIcons{
                display: flex;
                justify-content: center;
            }
            .socialIcons a{
                text-decoration: none;
                padding: 10px;
                background-color: white;
                margin: 10px;
                border-radius: 50%;
            }
            .socialIcons a i{
                font-size: 2em;
                color: black;
                opacity: 0.9;
            }
            .rent{
                color: darkorange;
                margin: 10px;
                display: flex;
                justify-content: center;
            }
            p{
                margin-top: 10px;
                display: flex;
                justify-content: center;
            }
            .phno{
                color: white;
            }
            .addr{
                color: white;
            }

            .aboutus{
                padding: 20px;
                background-color: bisque;
            }
            .why{
                color: #111;
                margin-left: 50px;
                padding: 0;
            }
            .para{
                margin-left: 50px;
            }

    </style>
</head>
<body>
    <nav>
    <div style="position: absolute; top: 0; left: 0; padding: 10px;">
  <img src="logo.png" alt="Logo" style="height: 50px;">
</div>

        <ul>
            <li><a href="ad_admin.php" class="active">Home</a></li>
            <li><a href="view_booking.php" class="active">View bookings</a></li>
            <li><a href="add_service.php" class="active">Add Service</a></li>
            <li><a href="remove_service.php" class="active">Remove Service</a></li>
            <li><a class="active" id="contact-btn">Contact Us</a></li>
        </ul> 
    </nav>
    <div class="home">
        <button><a class="logout" href="logout.php">LOGOUT</a></button>
    </div>
    <main>
        <div class="container">
            <div class="form-container">
                <h2>Add New Service</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-field">
                        <label for="service_name">Service Name:</label>
                        <input type="text" name="service_name" required>
                    </div>
                    <div class="form-field">
                        <label for="service_description">Service Description:</label>
                        <textarea name="service_description" required></textarea>
                    </div>
                    <div class="form-field">
                        <label for="service_image">Service Image:</label>
                        <input type="file" name="service_image" accept="image/*" required>
                    </div>
                    <div class="form-field">
                        <label for="service_price">Price (in Rs):</label>
                        <input type="text" name="service_price" required>
                    </div>
                    <input type="submit" value="Add Service">
                </form>
            </div>
        </div>
    </main>
    <footer>
        <div class="footerContainer">
            <div class="rent"><h1>SalonSpear</h1></div>
            <div class="socialIcons">
                <a href=""><i class="fa-brands fa-facebook"></i></a>
                <a href=""><i class="fa-brands fa-twitter"></i></a>
                <a href=""><i class="fa-brands fa-instagram"></i></a>
            </div>
            <div>
                <p class="phno">9846464646</p>
                <p class="addr">Salon Location</p>
            </div>
        </div>
    </footer>
</body>
</html>
