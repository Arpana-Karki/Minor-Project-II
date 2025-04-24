<?php
session_start();
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

// Function to get available salon services
function getAvailableServices() {
    global $conn;
    $services = array();

    $sql = "SELECT * FROM services WHERE is_available = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }
    return $services;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalonX</title>
    <link rel="stylesheet" href="stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
        }
        body {
            background-image: url('salon_back.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            width: 100%;
            height: 100%;
        }
        nav {
            background: linear-gradient(to right,hsl(0, 0.00%, 100.00%),rgb(199, 202, 178),rgb(227, 234, 92));
            height: 80px;
            width: 100%;
        }
        .cont {
            color: white;
            font-size: 35px;
            line-height: 80px;
            font-weight: bold;
            margin-left: 20px;
        }
        ul {
            
            float: right;
            margin-right: 100px;
            margin-top: -10px;
        }
        li {
            display: inline-block;
            line-height: 80px;
            margin: 0 5px;
        }
        li a {
            color: white;
            font-size: 16px;
            padding: 7px 13px;
            border-radius: 4px;
            text-transform: uppercase;
            border-bottom: 4px solid #FF8A00;
        }
        .active {

            text-decoration: none;
            background: transparent;
            transition: 0.5s;
        }
        .active:hover {
            background: #FF8A00;
        }
        .service-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 50px;
            border-radius: 10px;
        }
        .service-item {
            border: 2px groove grey;
            padding: 10px;
            border-radius: 10px;
            background-color: rgb(240, 240, 240);
        }
        .service-item:hover {
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
        }
        .service-image {
            width: 100%;
            height: auto;
        }
        .book-now-btn {
            background-color: coral;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .book-now-btn:hover {
            background-color: darkorange;
        }
    </style>
</head>
<body>
    <nav>
    <div style="position: absolute; top: 0; left: 0; padding: 10px; ">
  <img src="logo.png" alt="Logo" style="height: 50px;  ">
</div>

        <ul>
            <li><a href="ad_admin.php" class="active">Home</a></li>
            <li><a href="view_booking.php" class="active">View bookings</a></li>
            <li><a href="add_service.php" class="active">Add Service</a></li>
            <li><a href="remove_service.php" class="active">Remove Service</a></li>
            <li><a href="p.php" class="active">Send Promotion Mail</a></li>
            
        </ul>
    </nav>
   
    <div class="home">
        <button><a class="logout" href="logout.php"> LOGOUT </a></button>
    </div>

    <main>
        <?php
        // Get available services
        $availableServices = getAvailableServices();
        if (!empty($availableServices)) {
            echo '<div class="service-container">';
            foreach ($availableServices as $service) {
                echo '<div class="service-item">';
                // Display the service image
                echo '<img src="' . $service['image_path'] . '" alt="' . $service['name'] . '" class="service-image">';
                // Service name and description
                echo '<h3>' . $service['name'] . '</h3>';
                echo '<p>' . $service['description'] . '</p>';
                echo '<p><strong>Price: Rs. ' . $service['price'] . '</strong></p>';
                // Book now button
              ;
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>No services available at the moment.</p>';
        }
        ?>
    </main>

    <footer>
        <div class="footerContainer">
            <div class="socialIcons">
                <a href=""><i class="fa-brands fa-facebook"></i></a>
                <a href=""><i class="fa-brands fa-twitter"></i></a>
                <a href=""><i class="fa-brands fa-instagram"></i></a>
            </div>
            <div>
                <p>Call us: 123-456-7890</p>
                <p>Location: Main Street, City</p>
            </div>
        </div>
    </footer>

    <script>
            // for contact:
                const contact = document.querySelector("#contact-btn");

                const scrollDownToFooter = () => {
                    const footer = document.querySelector("footer");
                    footer.scrollIntoView({ behavior: "smooth" });
                }

                contact.addEventListener('click', scrollDownToFooter);
                mobile_nav.addEventListener('click', toggleNavbar);
                
            // JavaScript function to toggle the car description list
            function toggleDescription(arrowIcon) {
                var descriptionList = arrowIcon.parentNode.nextElementSibling;
                if (descriptionList.style.display === "none") {
                    descriptionList.style.display = "block";
                    arrowIcon.innerHTML = "<br>Car description: ⏪"; // Change to up arrow with text
                } else {
                    descriptionList.style.display = "none";
                    arrowIcon.innerHTML = "<br>Car description: ⏩"; // Change to down arrow with text
                }
            }
        </script>
</body>
</html>
