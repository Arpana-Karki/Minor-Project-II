<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve the user ID from the session
$userId = $_SESSION['user_id']; 

// Database configuration
$hostname = 'localhost';
$username = 'root'; 
$password = ''; 
$database = 'salon_db';  // Using salon_db as per your schema

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to cancel a booking
function cancelServiceBooking($bookingId, $userId) {
    global $conn;
    
    $sql = "DELETE FROM bookings WHERE id = ? AND user_id = ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bind_param("ii", $bookingId, $userId);
    
    // Execute the statement
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_button'])) {
    // Get the booking ID to be canceled
    $bookingId = $_POST['booking_id'];

    // Attempt to cancel the booking
    if (cancelServiceBooking($bookingId, $userId)) {
        echo "<script>
                alert('Service booking canceled successfully.');
                window.location.href='cancel_service.php';
              </script>";
    } else {
        echo "<script>
                alert('Error canceling the booking.');
                window.location.href='cancel_service.php';
              </script>";
    }
}

// Function to get user bookings
function getUserServiceBookings($userId) {
    global $conn;
    $bookings = array();

    $sql = "SELECT bookings.id, services.name as service_name, services.description as service_description, services.image_path, bookings.booking_date, bookings.booking_time, bookings.name as customer_name, bookings.phone as customer_phone
            FROM bookings
            JOIN services ON bookings.service_id = services.id
            WHERE bookings.user_id = ?";
            
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameter
    $stmt->bind_param("i", $userId);
    
    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Fetch bookings
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    return $bookings;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Service Booking</title>
    <style>
         *{
            margin: 0;  
        }
        h1{
            color:#fff;
        }
        body{
            background-image: url(back.jpg);
            background-repeat: no-repeat;
            background-size: cover;
            width: 100%;
            height: 100%;
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
            background: linear-gradient(to right,rgb(255, 255, 255),rgb(255, 255, 255),rgb(255, 255, 255));
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
        .car-details {
            position: relative;
        }

        .book-now-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
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
        .car-details {
            position: relative;
        }

        .book-now-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
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

        <style>
    main section {
        width: 80%;
        margin: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        margin-bottom: 200px;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    img {
        width: 100px;
        height: auto;
    }

    form {
        margin: 0;
    }

    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #45a049;
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
            /* .socialIcons a:hover i{
                color: white;
                transition: 0.5s;
            } */
            .socialIcons a i{
                font-size: 2em;
                color: black;
                opacity: 0.9;
            }
            /* .socialIcons a i:hover{
                background-color: #111;
                transition: 0.5s;
            } */
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
        <label class="cont">SalonSpear</label>
        <ul>
            <li><a href="home.php" class="active">Home</a></li>
            <li><a href="my_booking.php" class="active">My appointments</a></li>
            <li><a href="cancel_service.php" class="active">Cancel appointments</a></li>
            <li><a class="active" id="contact-btn">Contact Us</a></li>
        </ul>
    </nav>

    <div class="home">
        <button><a class="logout" href="logout.php"> LOGOUT </a></button>
    </div>

    <main>
    <section>
        <h2>Cancel Service Booking</h2>
        <?php
        // Get user service bookings
        $userServiceBookings = getUserServiceBookings($userId);

        if (!empty($userServiceBookings)) {
            echo '<table>';
            foreach ($userServiceBookings as $booking) {
                echo '<tr>';
                echo '<td><img src="' . $booking['image_path'] . '" alt="' . $booking['service_name'] . '" style="width: 100px;"></td>';
                echo '<td>' . $booking['service_name'] . '</td>';
                echo '<td>' . $booking['service_description'] . '</td>';
                echo '<td>' . $booking['booking_date'] . '</td>';
                echo '<td>' . $booking['booking_time'] . '</td>';
                echo '<td>' . $booking['customer_name'] . '</td>';
                echo '<td>' . $booking['customer_phone'] . '</td>';
                echo '<td>';
                echo '<form method="post" action="">';
                echo '<input type="hidden" name="booking_id" value="' . $booking['id'] . '">';
                echo '<input type="submit" name="cancel_button" value="Cancel Service">';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No service bookings available.</p>';
        }
        ?>
    </section>
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
                <p class="addr">Lamachaur, Pokhara</p>
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

    </script>
</body>
</html>
