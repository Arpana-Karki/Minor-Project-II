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
    <title>Salonspear - Style & Confidence</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #333;
            --accent-color: #f5a623;
            --light-gray: #f7f7f7;
            --dark-gray: #333;
            --white: #ffffff;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--white);
            color: var(--primary-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 2000px;
            margin: 0 auto;
            padding: 0 20px;
            
        }
        
        /* Header and Navigation */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            display: flex;
            align-items: left;
        }
        
        .logo img {
            height: 50px;
            margin-right: 2px;
        }
        
        .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-gray);
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 30px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--dark-gray);
            font-weight: 500;
            font-size: 16px;
            position: relative;
            transition: var(--transition);
        }
        
        .nav-links a:hover {
            color: var(--accent-color);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--accent-color);
            transition: var(--transition);
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .logout-btn {
            background-color: var(--accent-color);
            color: var(--white);
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .logout-btn:hover {
            background-color: #e09519;
            transform: translateY(-2px);
        }
        
        /* Hero Banner */
        .hero {
            margin-top: 70px;
            height: 400px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('salon_back.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-align: center;
        }
        
        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }
        
        .hero-content p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        /* Service Cards */
        .services-section {
            padding: 80px 0;
            background-color: var(--light-gray);
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background-color: var(--accent-color);
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .service-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .service-item {
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }
        
        .service-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .service-image-container {
            height: 200px;
            overflow: hidden;
        }
        
        .service-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        
        .service-item:hover .service-image {
            transform: scale(1.1);
        }
        
        .service-content {
            padding: 20px;
        }
        
        .service-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            margin-bottom: 10px;
            color: var(--dark-gray);
        }
        
        .service-content p {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .price {
            font-family: 'Playfair Display', serif;
            color: var(--accent-color);
            font-size: 20px;
            margin-bottom: 15px;
        }
        
        .book-now-btn {
            display: inline-block;
            background-color: var(--accent-color);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            text-align: center;
            text-decoration: none;
        }
        
        .book-now-btn:hover {
            background-color: #e09519;
            transform: translateY(-2px);
        }
        
        /* About Section */
        .about-section {
            padding: 80px 0;
            background-color: var(--white);
        }
        
        .about-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .feature-item {
            text-align: center;
            padding: 30px 20px;
            border-radius: 8px;
            background-color: var(--light-gray);
            transition: var(--transition);
        }
        
        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 36px;
            color: var(--accent-color);
            margin-bottom: 20px;
        }
        
        .feature-item h3 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark-gray);
            color: var(--white);
            padding: 60px 0 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 2px;
            background-color: var(--accent-color);
            bottom: 0;
            left: 0;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #ddd;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-links a:hover {
            color: var(--accent-color);
            padding-left: 5px;
        }
        
        .contact-info p {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .contact-info i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .footer-bottom {
            border-top: 1px solid #444;
            padding-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #aaa;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #444;
            border-radius: 50%;
            color: var(--white);
            transition: var(--transition);
        }
        
        .social-icons a:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
        }
        
        .newsletter {
            margin-top: 20px;
        }
        
        .newsletter form {
            display: flex;
            margin-top: 15px;
        }
        
        .newsletter input {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px 0 0 4px;
        }
        
        .newsletter button {
            background-color: var(--accent-color);
            border: none;
            color: var(--white);
            padding: 10px 15px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .newsletter button:hover {
            background-color: #e09519;
        }
        
        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--accent-color);
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }
        
        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero-content h1 {
                font-size: 36px;
            }
            
            .hero-content p {
                font-size: 16px;
            }
            
            .service-container {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="logo.png" alt="Salonspear Logo">
                   
                </div>
                <ul class="nav-links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="my_booking.php">My Appointments</a></li>
                     <!-- <li><a href="cancel_service.php">Cancel Appointments</a></li> -->
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="logout.php" class="logout-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero" style="background-image: url('bg.jpg'); background-size: cover; background-position: center; height: 100vh; display: flex; align-items: center; position: relative;">
    <div class="hero-content" style="background-color: rgba(255, 255, 255, 0.8); padding: 40px; border-radius: 10px; max-width: 600px; margin-left: 5%; font-family: 'Playfair Display', serif;">
        <h1 style="font-family: 'Great Vibes', cursive; color: #e8b23a; font-size: 3.5rem; margin-bottom: 20px;">Welcome to Salonspear</h1>
        <p style="font-size: 1.2rem; line-height: 1.6; color: #333;">Experience premium salon services tailored to enhance your natural beauty</p>

    </div>
</section>
    <section class="services-section" id="services">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            
            <?php
            // Get available services
            $availableServices = getAvailableServices();
            if (!empty($availableServices)) {
                echo '<div class="service-container">';
                foreach ($availableServices as $service) {
                    echo '<div class="service-item">';
                    // Display the service image
                    echo '<div class="service-image-container">';
                    echo '<img src="' . $service['image_path'] . '" alt="' . $service['name'] . '" class="service-image">';
                    echo '</div>';
                    
                    echo '<div class="service-content">';
                    // Service name and description
                    echo '<h3>' . $service['name'] . '</h3>';
                    echo '<p>' . $service['description'] . '</p>';
                    echo '<p class="price">Rs. ' . $service['price'] . '</p>';
                    // Book now button
                    echo '<a href="service_details.php?id=' . $service['id'] . '" class="book-now-btn">Book Now</a>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p class="no-services">No services available at the moment.</p>';
            }
            ?>
        </div>
    </section>

    <section class="about-section" id="about">
        <div class="container">
            <h2 class="section-title">Why Choose Salonspear?</h2>
            <div class="about-content">
                <div class="feature-grid">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-cut"></i>
                        </div>
                        <h3>Premium Services</h3>
                        <p>We offer a wide range of beauty and wellness services, from trendy haircuts to rejuvenating spa treatments.</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3>Easy Booking</h3>
                        <p>Our user-friendly online platform allows you to book appointments effortlessly, anytime, anywhere.</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3>Expert Stylists</h3>
                        <p>Our team of highly trained professionals is committed to delivering top-quality services.</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Hygiene & Safety</h3>
                        <p>We follow strict cleanliness protocols and maintain the highest safety standards.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">

<h2 style="text-align: center; margin-bottom: 20px; color: #333; font-size: 24px;">What Our Customers Say</h2>

<div style="max-width: 100%; overflow: hidden; position: relative; margin: 30px auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px; background-color: white; padding: 20px;">
  <div style="display: flex; width: max-content; animation: scroll 30s linear infinite;">
    <!-- Review 1 -->
    <div style="width: 300px; padding: 20px; margin-right: 20px; background-color: #f9f9f9; border-radius: 6px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); flex-shrink: 0;">
      <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ddd; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">JD</div>
        <div style="flex-grow: 1;">
          <div style="font-weight: bold; margin-bottom: 3px;">John Doe</div>
          <div style="font-size: 12px; color: #777;">March 15, 2025</div>
        </div>
      </div>
      <div style="color: #FFD700; font-size: 18px; margin-bottom: 10px;">★★★★★</div>
      <p style="color: #444; line-height: 1.5;">Absolutely love this product! It has completely changed my daily routine. The quality is outstanding and customer service was top-notch. Highly recommended!</p>
    </div>

    <!-- Review 2 -->
    <div style="width: 300px; padding: 20px; margin-right: 20px; background-color: #f9f9f9; border-radius: 6px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); flex-shrink: 0;">
      <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ddd; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">SM</div>
        <div style="flex-grow: 1;">
          <div style="font-weight: bold; margin-bottom: 3px;">Sarah Miller</div>
          <div style="font-size: 12px; color: #777;">February 28, 2025</div>
        </div>
      </div>
      <div style="color: #FFD700; font-size: 18px; margin-bottom: 10px;">★★★★☆</div>
      <p style="color: #444; line-height: 1.5;">Great experience overall. The product arrived on time and works as described. Would have given 5 stars but I had a small issue with installation. Support helped resolve it quickly.</p>
    </div>

    <!-- Review 3 -->
    <div style="width: 300px; padding: 20px; margin-right: 20px; background-color: #f9f9f9; border-radius: 6px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); flex-shrink: 0;">
      <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ddd; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">RJ</div>
        <div style="flex-grow: 1;">
          <div style="font-weight: bold; margin-bottom: 3px;">Robert Johnson</div>
          <div style="font-size: 12px; color: #777;">March 5, 2025</div>
        </div>
      </div>
      <div style="color: #FFD700; font-size: 18px; margin-bottom: 10px;">★★★★★</div>
      <p style="color: #444; line-height: 1.5;">I've tried many similar products but this one stands out. The attention to detail is impressive and it's clear that a lot of thought went into the design. Worth every penny!</p>
    </div>

    <!-- Review 4 -->
    <div style="width: 300px; padding: 20px; margin-right: 20px; background-color: #f9f9f9; border-radius: 6px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); flex-shrink: 0;">
      <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ddd; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">AL</div>
        <div style="flex-grow: 1;">
          <div style="font-weight: bold; margin-bottom: 3px;">Amy Lee</div>
          <div style="font-size: 12px; color: #777;">March 10, 2025</div>
        </div>
      </div>
      <div style="color: #FFD700; font-size: 18px; margin-bottom: 10px;">★★★★★</div>
      <p style="color: #444; line-height: 1.5;">Exceptional quality and value. I was skeptical at first but after using it for a month, I'm completely sold. Will definitely be purchasing again in the future.</p>
    </div>

    <!-- Review 5 -->
    <div style="width: 300px; padding: 20px; margin-right: 20px; background-color: #f9f9f9; border-radius: 6px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); flex-shrink: 0;">
      <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ddd; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">MT</div>
        <div style="flex-grow: 1;">
          <div style="font-weight: bold; margin-bottom: 3px;">Mike Thompson</div>
          <div style="font-size: 12px; color: #777;">February 20, 2025</div>
        </div>
      </div>
      <div style="color: #FFD700; font-size: 18px; margin-bottom: 10px;">★★★☆☆</div>
      <p style="color: #444; line-height: 1.5;">Good product for the price. It does what it promises but I feel there's room for improvement in the user interface. Overall satisfied with my purchase.</p>
    </div>

    <!-- Review 6 -->
    <div style="width: 300px; padding: 20px; margin-right: 20px; background-color: #f9f9f9; border-radius: 6px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); flex-shrink: 0;">
      <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ddd; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">KW</div>
        <div style="flex-grow: 1;">
          <div style="font-weight: bold; margin-bottom: 3px;">Kelly Wilson</div>
          <div style="font-size: 12px; color: #777;">March 18, 2025</div>
        </div>
      </div>
      <div style="color: #FFD700; font-size: 18px; margin-bottom: 10px;">★★★★★</div>
      <p style="color: #444; line-height: 1.5;">I'm completely blown away by the quality and performance. This exceeded all my expectations and I've already recommended it to all my friends. A game-changer!</p>
    </div>

  </div>
</div>

</body>
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Salonspear</h3>
                    <p>Experience the perfect blend of style, comfort, and care at Salonspear - where beauty meets excellence.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="home.php">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="my_booking.php">My Appointments</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> Lamachaur, Pokhara, Nepal</p>
                        <p><i class="fas fa-phone"></i> +977 9846464646</p>
                        <p><i class="fas fa-envelope"></i> info@salonspear.com</p>
                        <p><i class="fas fa-clock"></i> Mon-Sat: 9:00 AM - 8:00 PM</p>
                    </div>
                </div>
             
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Salonspear. All Rights Reserved. Designed with <i class="fas fa-heart" style="color: var(--accent-color);"></i></p>
            </div>
        </div>
    </footer>

    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </a>

    <script>
        // Back to top button
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Animation for service cards on scroll
        const serviceItems = document.querySelectorAll('.service-item');
        
        const observerOptions = {
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        serviceItems.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(item);
        });
    </script>
</body>
</html>