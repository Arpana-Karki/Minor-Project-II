<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$servername = "localhost"; // or your database server IP
$username = "root";        // your database username
$password = "";            // your database password (default is empty for XAMPP)
$dbname = "easy_living";   // your database name

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $full_name = $_SESSION["full_name"];
    $role = isset($_SESSION["role"]) ? $_SESSION["role"] : "customer";  // Default to "customer" if role is not set
} else {
    header("Location: login.php");  // Redirect to login page if the session is not set
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EasyLiving - Home</title>
    <link rel="stylesheet" href="./style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400&display=swap" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="./Owl.carousel.min.css" />
    <link rel="stylesheet" href="./Owl.theme.default.min.css" />
    <script src="./Owl.carousel.min.js"></script>
  </head>
  <body>
    <!-- Include Navbar -->
    <nav class="navbar">
      <div class="container">
        <a href="index.php" class="navbar-logo">EasyLiving</a>
        <ul class="navbar-links">
          <li><a href="home.php">Home</a></li>
          <li><a href="about.html">About</a></li>
          <li><a href="services.php">Services</a></li>
          <li><a href="index.php">Logout</a></li>
          <li><a href="cart.php">Cart</a></li>
        </ul>
      </div>
    </nav>

    <!-- Header Section -->
    <header class="hero">
      <div class="container">
        <h1 class="hero-title">Welcome, <?php echo htmlspecialchars($full_name); ?>!</h1>
        <p class="hero-subtitle">You are logged in as <?php echo htmlspecialchars($role); ?>.</p>
        <a href="services.php" class="btn hero-btn">Explore Our Services</a>
        <div class="hero-image">
          <img src="./image/twooPM.jpeg" alt="Hero Image" />
        </div>
      </div>
    </header>

    <!-- Why Choose Us Section -->
    <section class="about">
      <div class="container">
        <h2>Why Choose Us?</h2>
        <p>
          We connect you with verified professionals offering various services,
          ensuring convenience and trust every time.
        </p>
        <div class="features">
          <div class="feature">
            <i class="fas fa-check-circle"></i>
            <h3>Trusted Professionals</h3>
            <p>Our service providers are carefully vetted for quality and reliability.</p>
            <div class="feature-image">
              <img src="./image/5people.jpeg" alt="Feature Image" />
            </div>
          </div>
          <div class="feature">
            <i class="fas fa-clock"></i>
            <h3>Flexible Scheduling</h3>
            <p>Choose the time that works best for you, and we’ll be there!</p>
            <div class="feature-image">
              <img src="./image/flexible.jpeg" alt="Feature Image" />
            </div>
          </div>
          <div class="feature">
            <i class="fas fa-thumbs-up"></i>
            <h3>Quality Assurance</h3>
            <p>We strive for excellence in every service, with your satisfaction as our priority.</p>
            <div class="feature-image">
              <img src="./image/quality.jpeg" alt="Feature Image" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section class="services-preview">
      <div class="container">
        <h2>Our Popular Package</h2>
        <div class="service-cards">
          <div class="service-card">
            <i class="fas fa-plumbing"></i>
            <h3>Plumbing & Electrician</h3>
            <p>Leaky faucets, pipe repairs, wiring, and more – let us handle it!</p>
            <div class="service-image">
              <img src="./image/electric.jpeg" alt="Service Image" />
            </div>
            <a href="services.php" class="btn">Learn More</a>
          </div>
          <div class="service-card">
            <i class="fas fa-broom"></i>
            <h3>Home Cleaning & Laundry</h3>
            <p>Our professionals will leave your home spotless.</p>
            <div class="service-image">
              <img src="./image/clen.jpeg" alt="Service Image" />
            </div>
            <a href="services.php" class="btn">Learn More</a>
          </div>
          <div class="service-card">
            <i class="fas fa-paint-roller"></i>
            <h3>Cook & Decoration</h3>
            <p>Professional decoration services to refresh your space.</p>
            <div class="service-image">
              <img src="./image/cook.jpeg" alt="Service Image" />
            </div>
            <a href="services.php" class="btn">Learn More</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonial Section -->
    <div class="testimonial">
      <div class="container">
        <div class="section-header">
          <p>Client Review</p>
          <h2>Client Says About Service</h2>
        </div>
        <div class="owl-carousel testimonials-carousel">
          <!-- Add testimonial items here -->
        </div>
      </div>
    </div>

    <!-- Call to Action Section -->
    <section class="cta">
      <div class="container">
        <h2>Ready to Book?</h2>
        <p>Get started with your service request today!</p>
        <a href="services.php" class="btn">Explore Services</a>
      </div>
    </section>

    <!-- Footer Section -->
    <footer>
      <div class="container">
        <p>&copy; 2025 EasyLiving. All rights reserved.</p>
        <p>
          <a href="about.php">About Us</a> |
          <a href="services.php">Services</a> |
          <a href="contact.php">Contact</a>
        </p>
      </div>
    </footer>
    <script src="./mini.js"></script>
  </body>
</html>
