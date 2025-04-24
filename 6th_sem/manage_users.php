<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'salon_db';  // Make sure to change to your actual database name

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        /* Google Fonts - Poppins */
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");
* {
            margin: 0;
        }
        body{
            background-image: url(back.jpg);
            background-repeat: no-repeat;
            background-size: cover;
            width: 100%;
            height: 100%;
        }
        .template{
            background-image: url(template.jpg);
            height: 500px;
            /* background-repeat: no-repeat; */
            background-size: 100% 100%;
        }
        main{
            margin-top: -150px;
        }
        h1 {
            color: #fff;
        }

        .home {
            font-weight: 900 !important;
        }

        .logout {
            text-decoration: none !important;
            color: #fff !important;
            background-color: #3200a0 !important;
            padding: 10px;
            border: 2px solid transparent;
            border-radius: 14px;
            top: 90%;
            right: 20px;
            position: fixed;
        }

        .logout:hover {
            background-color: rgb(243, 134, 95) !important;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
        }

        button {
            border: none;
            float: right;
            background: none;
        }

        nav {
            background: linear-gradient(to right, #219efc, #64DFDF, #48BFE3, #5390D9, #5E60CE);
            height: 80px;
            width: 100%;
        }

        .cont {
            color: white;
            font-size: 35px;
            line-height: 80px;
            padding: 0px;
            font-weight: bold;
            margin-left: 20px;
        }

        .cont:hover {
            color: coral;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
            text-shadow: 0 0 3px #FF0000;
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
            border-bottom: 4px solid coral;
        }

        .active {
            text-decoration: none;
            background: transparent;
            transition: 0.5s;
        }

        .active:hover {
            background: coral;
            box-shadow: 8px 8px 10px 0px rgba(0, 0, 0, 0.5);
        }
        /* Form container styling */
        .form-container {
        position: relative;
        max-width: 600px;
        margin: 80px auto;
        padding: 20px;
        background: linear-gradient(rgb(180, 212, 246), #a9dded, #8ac9e3, rgb(108, 159, 199));
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

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
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
              margin-top: 200px;
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
            <li><a href="admin_dashboard.php" class="active">Home</a></li>
            <li><a href="add_service.php" class="active">Add Service</a></li>
            <li><a href="manage_users.php" class="active">Manage Users</a></li>
        </ul> 
    </nav>

    <section>
        <h1 style="color: black">Registered Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Execute a SELECT query to retrieve user data
                $sql = "SELECT id, full_name, username, email FROM registered_users";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . $row['full_name'] . '</td>';
                        echo '<td>' . $row['username'] . '</td>';
                        echo '<td>' . $row['email'] . '</td>';
                        echo '<td><a href="edit_user.php?id=' . $row['id'] . '">Edit</a> | <a href="delete_user.php?id=' . $row['id'] . '" onclick="return confirm(\'Are you sure?\')">Delete</a></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">No registered users found.</td></tr>';
                }

                // Close connection
                $conn->close();
                ?>
            </tbody>
        </table>
    </section>

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
                <p class="addr">Pokhara</p>
            </div>
        </div>
    </footer>

  </body>
</html>
