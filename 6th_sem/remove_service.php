<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
$hostname = 'localhost'; 
$username = 'root'; 
$password = ''; 
$database = 'salon_db'; // Updated database name

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_button'])) {
    // Get the service ID to be removed
    $serviceId = $_POST['service_id'];

    // Prepare and execute the SQL query to delete the service
    $sql = "DELETE FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $stmt->close();

        // Redirect to the remove_service.php page after deletion
        header("Location: remove_service.php");
        exit();
    } else {
        echo "Error in prepared statement: " . $conn->error;
    }
}

// Function to fetch all services for display
function getAllServices() {
    global $conn;
    $services = array();

    $sql = "SELECT * FROM services";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }

    return $services;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="scripts.js"></script>
    <style>
        /* CSS Styles, same as in remove_car.php */
/* Reset and base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
}

/* Main section styles */
section {
    background-image: url(back.jpg);
    background-repeat: no-repeat;
    background-size: cover;
    background-attachment: fixed;
    min-height: 100vh;
    padding: 2rem;
}

/* Navigation styles */
nav {
    background: linear-gradient(to right,hsl(0, 0.00%, 100.00%),rgb(199, 202, 178),rgb(227, 234, 92));
    padding: 0 2rem;
    height: 80px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.cont {
    color: white;
    font-size: 35px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.cont:hover {
    color: coral;
    text-shadow: 0 0 3px #FF0000;
}

nav ul {
    display: flex;
    gap: 1.5rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

nav li a {
    color: white;
    font-size: 16px;
    padding: 7px 13px;
    border-radius: 4px;
    text-transform: uppercase;
    text-decoration: none;
    border-bottom: 4px solid coral;
    transition: all 0.3s ease;
}

.active:hover {
    background: coral;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Service list styles */
.service-list {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.service-item {
    background-color: rgba(241, 236, 235, 0.95);
    border: none;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.service-item p {
    margin: 0;
    font-size: 1.25rem;
    color: #333;
    font-weight: 500;
}

/* Remove button styles */
.remove-btn {
    background-color: #ff4444;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.remove-btn:hover {
    background-color: #cc0000;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Logout button styles */
.logout {
    text-decoration: none;
    color: white;
    background-color: #3200a0;
    padding: 0.75rem 1.5rem;
    border-radius: 14px;
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.logout:hover {
    background-color: rgb(243, 134, 95);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* Empty state styles */
.no-services {
    text-align: center;
    color: white;
    font-size: 1.25rem;
    padding: 2rem;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 8px;
    margin: 2rem auto;
    max-width: 600px;
}

/* Responsive design */
@media (max-width: 1024px) {
    .service-list {
        max-width: 900px;
    }
    
    .service-item {
        margin-left: 0;
        width: 100%;
    }
}

@media (max-width: 768px) {
    nav {
        flex-direction: column;
        height: auto;
        padding: 1rem;
    }

    nav ul {
        flex-direction: column;
        width: 100%;
        text-align: center;
        gap: 0.5rem;
        margin: 1rem 0;
    }

    nav li {
        width: 100%;
    }

    nav li a {
        display: block;
        width: 100%;
        margin: 0.25rem 0;
    }

    .service-item {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .service-item p {
        font-size: 1.1rem;
    }

    .logout {
        position: static;
        display: block;
        margin: 1rem auto;
        text-align: center;
    }
}     
    </style>
</head>
<body>
    <nav>
        <label class="cont">SalonSpear</label>
        <ul>
            <li><a href="ad_admin.php" class="active">Home</a></li>
            <li><a href="view_booking.php" class="active">View bookings</a></li>
            <li><a href="add_service.php" class="active">Add Service</a></li>
            <li><a href="remove_service.php" class="active">Remove Service</a></li>
            <li><a class="active" id="contact-btn">Contact Us</a></li>
        </ul> 
    </nav>
    <div class="home">
        <button><a class="logout" href="logout.php"> LOGOUT </a></button>
    </div>

    <main>
        <section>
            <?php
            // Display the list of services
            $servicesList = getAllServices();

            if (!empty($servicesList)) {
                echo '<ul class="service-list">';
                foreach ($servicesList as $service) {
                    echo '<li class="service-item">';
                    echo '<p>' . $service['name'] . ' - ' . $service['price'] . '</p>';
                    echo '<form method="post" action="remove_service.php">';
                    echo '<input type="hidden" name="service_id" value="' . $service['id'] . '">';
                    echo '<input class="remove-btn" type="submit" name="remove_button" value="Remove">';
                    echo '</form>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No services available.</p>';
            }
            ?>
        </section>
    </main>
</body>
</html>
