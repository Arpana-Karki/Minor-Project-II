<?php
// Start the session or any PHP logic needed
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EasyLiving - About Us</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1f2937;
            overflow-x: hidden;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #4f46e5;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        .action-btn {
            background: linear-gradient(to right, #4f46e5, #7c3aed);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        .action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .animate-scale-in {
            animation: scaleIn 0.5s ease-out forwards;
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        /* Hide menu toggle by default */
        .menu-toggle {
            display: none;
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            position: relative;
            background: url("./image/2people.jpeg") no-repeat center center/cover;
            color: white;
            text-align: center;
            padding: 8rem 1.5rem;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            animation: fadeIn 1.5s ease-in-out;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
        }

        .hero p {
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Container */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            padding: 3rem 1rem;
        }

        /* Section */
        .section {
            margin-bottom: 3rem;
            padding: 2.5rem;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            animation: slideUp 1s ease-in-out;
        }

        .section h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            color: #1e3a8a;
            position: relative;
        }

        .section h2::after {
            content: '';
            width: 60px;
            height: 4px;
            background: #3b82f6;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .about-text {
            font-size: 1.1rem;
            line-height: 1.8;
            text-align: justify;
            color: #4b5563;
        }

        /* Team Section */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .team-card {
            text-align: center;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 1rem;
            transition: transform 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .team-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .team-card h3 {
            font-size: 1.25rem;
            color: #1e3a8a;
            margin-bottom: 0.5rem;
        }

        .team-card p {
            font-size: 0.9rem;
            color: #6b7280;
        }

        /* Services Table */
        .service-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .service-table th,
        .service-table td {
            padding: 1rem;
            text-align: center;
        }

        .service-table th {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 500;
        }

        .service-table td {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .service-table tr:last-child td {
            border-bottom: none;
        }

        .service-table tr:hover td {
            background: #e0f2fe;
        }

        /* FAQ Section */
        .faq-item {
            margin-bottom: 1rem;
        }

        .faq-question {
            background: #3b82f6;
            color: #ffffff;
            border: none;
            width: 100%;
            padding: 1rem;
            text-align: left;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question:hover {
            background: #2563eb;
        }

        .faq-question i {
            transition: transform 0.3s ease;
        }

        .faq-question.active i {
            transform: rotate(180deg);
        }

        .faq-answer {
            display: none;
            padding: 1rem;
            background: #f1f5f9;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
            font-size: 1rem;
            color: #4b5563;
        }

        /* Testimonials Section */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .testimonial-card {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
        }

        .testimonial-card p {
            font-size: 1rem;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .testimonial-card h4 {
            font-size: 1.1rem;
            color: #1e3a8a;
            font-weight: 500;
        }

        /* Contact Section */
        .contact {
            text-align: center;
            padding: 3rem;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: #ffffff;
            border-radius: 1rem;
            margin-top: 3rem;
            position: relative;
            overflow: hidden;
        }

        .contact h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .contact p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .contact .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #ffffff;
            color: #1e3a8a;
            text-decoration: none;
            font-weight: 500;
            border-radius: 999px;
            transition: all 0.3s ease;
        }

        .contact .btn:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar ul {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #f8f9fa;
                padding: 1rem;
            }

            .navbar ul.active {
                display: flex;
            }

            .menu-toggle {
                display: block;
                font-size: 1.5rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section {
                padding: 1.5rem;
            }

            .section h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-indigo-600 animate-scale-in">EasyLiving</a>
            <div class="flex items-center space-x-6">
                <ul class="flex space-x-6 text-gray-700">
                    <li><a href="index.php" class="nav-link hover:text-indigo-600">Home</a></li>
                    <li><a href="about.php" class="nav-link hover:text-indigo-600">About</a></li>
                    <li><a href="./customer/package.php" class="nav-link hover:text-indigo-600">Packages</a></li>
                    <li><a href="./customer/subservice.php" class="nav-link hover:text-indigo-600">Services</a></li>
                </ul>
                <!-- Wishlist -->
                <a href="customer/favorites.php" class="action-btn">
                    <i class="fas fa-heart mr-2"></i>  Favorites
                </a>
                <!-- My Bookings -->
                <a href="customer/my_bookings.php" class="action-btn">
                    <i class="fas fa-calendar-check mr-2"></i> My Bookings
                </a>
                <!-- My Profile -->
                <a href="profile.php" class="action-btn">
                    <i class="fas fa-user-circle mr-2"></i> My Profile
                </a>
                <div class="menu-toggle text-gray-700"><i class="fas fa-bars"></i></div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-content">
            <h1>About EasyLiving</h1>
            <p>Transforming homes with exceptional services, delivered with care and professionalism.</p>
        </div>
    </div>

    <div class="container">
        <!-- About Section -->
        <div class="section">
            <h2>Our Story</h2>
            <p class="about-text">
                At EasyLiving, we believe in making life simpler and more comfortable. Founded with a passion for excellence, we offer a wide range of home and professional services, from cleaning and repairs to gardening and event decoration. Our team of certified professionals is committed to delivering top-notch quality, ensuring your home is a haven of comfort and beauty.
            </p>
        </div>

        <!-- Team Section -->
        <div class="section">
            <h2>Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-card">
                    <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Team Member">
                    <h3>Ram Shrestha</h3>
                    <p>Founder & CEO</p>
                </div>
                <div class="team-card">
                    <img src="https://randomuser.me/api/portraits/women/2.jpg" alt="Team Member">
                    <h3>Sita Gurung</h3>
                    <p>Operations Manager</p>
                </div>
                <div class="team-card">
                    <img src="https://randomuser.me/api/portraits/men/3.jpg" alt="Team Member">
                    <h3>Hari Tamang</h3>
                    <p>Lead Technician</p>
                </div>
            </div>
        </div>

        <!-- Services Table -->
        <div class="section">
            <h2>Our Services</h2>
            <table class="service-table">
                <tr>
                    <th>Service</th>
                    <th>Description</th>
                    <th>Availability</th>
                </tr>
                <tr>
                    <td>Home Cleaning</td>
                    <td>Deep cleaning for homes and offices.</td>
                    <td>Available</td>
                </tr>
                <tr>
                    <td>Electrician Services</td>
                    <td>Electrical installations and repairs.</td>
                    <td>Available</td>
                </tr>
                <tr>
                    <td>Plumbing</td>
                    <td>Fixing leaks, pipe installations.</td>
                    <td>Available</td>
                </tr>
                <tr>
                    <td>Decoration</td>
                    <td>Birthday, Anniversary and Parties.</td>
                    <td>On Request</td>
                </tr>
                <tr>
                    <td>Gardening</td>
                    <td>Landscaping and plant maintenance.</td>
                    <td>Available</td>
                </tr>
                <tr>
                    <td>Farming</td>
                    <td>Agricultural and farming solutions.</td>
                    <td>On Request</td>
                </tr>
            </table>
        </div>

        <!-- Testimonials Section -->
        <div class="section">
            <h2>What Our Clients Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p>"EasyLiving transformed my home with their exceptional cleaning service. Highly professional and reliable!"</p>
                    <h4>Priya Sharma</h4>
                </div>
                <div class="testimonial-card">
                    <p>"The plumbing service was quick and efficient. The team was courteous and skilled."</p>
                    <h4>Rajesh Thapa</h4>
                </div>
                <div class="testimonial-card">
                    <p>"Their gardening service brought my backyard to life. Absolutely stunning work!"</p>
                    <h4>Anita Rai</h4>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq">
                <div class="faq-item">
                    <button class="faq-question">What areas do you serve? <i class="fas fa-chevron-down"></i></button>
                    <div class="faq-answer">We currently serve Kathmandu, Pokhara, and surrounding areas.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">How do I book a service? <i class="fas fa-chevron-down"></i></button>
                    <div class="faq-answer">You can book a service through our website by selecting a service and filling in the booking form.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">What are the payment options? <i class="fas fa-chevron-down"></i></button>
                    <div class="faq-answer">We accept Khalti, eSewa, and Cash on Delivery.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">Are your professionals certified? <i class="fas fa-chevron-down"></i></button>
                    <div class="faq-answer">Yes, all our service providers are trained and certified in their respective fields.</div>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact">
            <h2>Ready to Transform Your Home?</h2>
            <p>Contact us at <strong>061554362</strong> or <strong>support@easyLiving.com</strong></p>
            <a href="booking.php" class="btn">Book Now</a>
        </div>
    </div>

    <script>
        // FAQ Toggle
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', function () {
                const answer = this.nextElementSibling;
                const isActive = answer.style.display === 'block';
                document.querySelectorAll('.faq-answer').forEach(ans => ans.style.display = 'none');
                document.querySelectorAll('.faq-question').forEach(btn => btn.classList.remove('active'));
                if (!isActive) {
                    answer.style.display = 'block';
                    this.classList.add('active');
                }
            });
        });

        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.navbar ul');
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Animation on Scroll
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.section, .team-card, .testimonial-card').forEach(el => {
            el.classList.add('animate-on-scroll');
            observer.observe(el);
        });

        // Add animate class for CSS
        const style = document.createElement('style');
        style.innerHTML = `
            .animate-on-scroll { opacity: 0; transform: translateY(30px); transition: all 0.6s ease; }
            .animate-on-scroll.animate { opacity: 1; transform: translateY(0); }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>