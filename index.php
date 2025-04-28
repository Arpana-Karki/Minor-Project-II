
<?php
// Start the session or any PHP logic needed
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyLiving - Home</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Custom Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        .animate-scale-in {
            animation: scaleIn 0.5s ease-out forwards;
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
        /* Dropdown Styling */
        .dropdown:hover .dropdown-menu {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        .dropdown-menu {
            display: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            background: white;
        }
        .dropdown-menu li a {
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .dropdown-menu li a:hover {
            background: #e0e7ff;
            transform: translateX(5px);
        }
        /* Button Styling */
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
        /* Hover Effects for Cards */
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
        /* Swiper Slider Styling */
        .testimonial-slider {
            padding-bottom: 50px; /* Space for pagination */
            position: relative;
        }
        .testimonial-slider .swiper-slide {
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        .testimonial-slider .swiper-slide-active {
            opacity: 1;
        }
        .testimonial-slider .swiper-button-prev,
        .testimonial-slider .swiper-button-next {
            color: #4f46e5;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }
        /* .testimonial-slider .swiper-button-prev:hover,
        .testimonial-slider .swiper-button-next:hover {
            background: #4f46e5;
            color: white;
        } */
        .testimonial-slider .swiper-pagination {
            position: absolute;
            bottom: 10px;
        }
        .testimonial-slider .swiper-pagination-bullet {
            background: #4f46e5;
            opacity: 0.5;
        }
        .testimonial-slider .swiper-pagination-bullet-active {
            opacity: 1;
        }
        /* Hide Swiper elements outside testimonial section */
        .swiper-button-prev:not(.testimonial-slider .swiper-button-prev),
        .swiper-button-next:not(.testimonial-slider .swiper-button-next),
        .swiper-pagination:not(.testimonial-slider .swiper-pagination) {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <!-- Navbar -->
    <nav class="navbar sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-indigo-600 animate-scale-in">EasyLiving</a>
            <div class="flex items-center space-x-6">
                <ul class="flex space-x-6 text-gray-700">
                    <li><a href="index.php" class="nav-link hover:text-indigo-600">Home</a></li>
                    <li><a href="about.php" class="nav-link hover:text-indigo-600">About</a></li>
                    <li><a href="./customer/customer_package.php" class="nav-link hover:text-indigo-600">Packages</a></li>
                    <li><a href="./customer/subservice.php" class="nav-link hover:text-indigo-600">Services</a></li>
                </ul>
                <!-- Wishlist -->
                <a href="customer/favorites.php" class="action-btn">
                    <i class="fas fa-heart mr-2"></i> Wishlist
                </a>
                <!-- My Bookings -->
                <a href="customer/my_bookings.php" class="action-btn">
                    <i class="fas fa-calendar-check mr-2"></i> My Bookings
                </a>
                <!-- My Profile -->
                <a href="profile.php" class="action-btn">
                    <i class="fas fa-user-circle mr-2"></i> My Profile
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20">
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 animate-fade-in-up">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Your Trusted Service Provider</h1>
                <p class="text-lg mb-6">Professional services at your doorstep, anytime, anywhere.</p>
                <a href="customer/subservice.php" class="bg-yellow-400 text-black px-6 py-3 rounded-full font-semibold hover:bg-yellow-500 transition transform hover:scale-105">Explore Our Services</a>
            </div>
            <div class="md:w-1/2 mt-8 md:mt-0">
                <img src="./image/twooPM.jpeg" alt="Hero Image" class="w-3/4 mx-auto rounded-lg shadow-lg">
            </div>
        </div>
    </section>

    <!-- Our Popular Package Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 animate-fade-in-up">Our Popular Package</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="service-card bg-gray-50 p-6 rounded-lg shadow-md transition transform animate-fade-in-up">
                    <i class="fas fa-plumbing text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Plumbing & Electrician</h3>
                    <p class="text-gray-600 mb-4">Leaky faucets, pipe repairs, wiring, and more – let us handle it!</p>
                    <img src="./image/electric.jpeg" alt="Service Image" class="w-full h-40 object-cover rounded-lg mb-4">
                    <a href="./customer/customer_package.php" class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700 transition">Learn More</a>
                </div>
                <div class="service-card bg-gray-50 p-6 rounded-lg shadow-md transition transform animate-fade-in-up">
                    <i class="fas fa-broom text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Home Cleaning & Laundry</h3>
                    <p class="text-gray-600 mb-4">Our professionals will leave your home spotless.</p>
                    <img src="./image/clen.jpeg" alt="Service Image" class="w-full h-40 object-cover rounded-lg mb-4">
                    <a href="./customer/customer_package.php" class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700 transition">Learn More</a>
                </div>
                <div class="service-card bg-gray-50 p-6 rounded-lg shadow-md transition transform animate-fade-in-up">
                    <i class="fas fa-paint-roller text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Cook & Decoration</h3>
                    <p class="text-gray-600 mb-4">Professional Decoration services to refresh your space.</p>
                    <img src="./image/cook.jpeg" alt="Service Image" class="w-full h-40 object-cover rounded-lg mb-4">
                    <a href="./customer/customer_package.php" class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700 transition">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 animate-fade-in-up">Why Choose Us?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md transition transform hover:shadow-lg animate-fade-in-up">
                    <i class="fas fa-check-circle text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Trusted Professionals</h3>
                    <p class="text-gray-600 mb-4">Our service providers are carefully vetted for quality and reliability.</p>
                    <img src="./image/5people.jpeg" alt="Feature Image" class="w-full h-40 object-cover rounded-lg">
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md transition transform hover:shadow-lg animate-fade-in-up">
                    <i class="fas fa-clock text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Flexible Scheduling</h3>
                    <p class="text-gray-600 mb-4">Choose the time that works best for you, and we’ll be there!</p>
                    <img src="./image/flexible.jpeg" alt="Feature Image" class="w-full h-40 object-cover rounded-lg">
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md transition transform hover:shadow-lg animate-fade-in-up">
                    <i class="fas fa-thumbs-up text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Quality Assurance</h3>
                    <p class="text-gray-600 mb-4">We strive for excellence in every service, with your satisfaction as our priority.</p>
                    <img src="./image/quality.jpeg" alt="Feature Image" class="w-full h-40 object-cover rounded-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section class="py-16 bg-gray-200">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 animate-fade-in-up">Client Says About Our Services</h2>
            <div class="swiper-container testimonial-slider">
                <div class="swiper-wrapper">
                    <!-- Testimonial 1 -->
                    <div class="swiper-slide">
                        <div class="bg-white p-6 rounded-lg shadow-md text-center mx-4">
                            <img src="./image/balen.jpeg" alt="Client" class="w-20 h-20 rounded-full mx-auto mb-4">
                            <p class="text-gray-600 mb-4">"Easy Living provides exceptional services! I booked an electrician, and the work was done professionally and on time."</p>
                            <h3 class="font-semibold">Balen Shah</h3>
                            <p class="text-gray-500">Mayor of Kathmandu</p>
                        </div>
                    </div>
                    <!-- Testimonial 2 -->
                    <div class="swiper-slide">
                        <div class="bg-white p-6 rounded-lg shadow-md text-center mx-4">
                            <img src="./image/pra.jpeg" alt="Client" class="w-20 h-20 rounded-full mx-auto mb-4">
                            <p class="text-gray-600 mb-4">"Reliable, efficient, and professional! The home cleaning service was outstanding."</p>
                            <h3 class="font-semibold">Parash Khadka</h3>
                            <p class="text-gray-500">Former Cricketer</p>
                        </div>
                    </div>
                    <!-- Testimonial 3 -->
                    <div class="swiper-slide">
                        <div class="bg-white p-6 rounded-lg shadow-md text-center mx-4">
                            <img src="./image/pri.jpeg" alt="Client" class="w-20 h-20 rounded-full mx-auto mb-4">
                            <p class="text-gray-600 mb-4">"I was impressed by the seamless experience of booking a home cleaning service."</p>
                            <h3 class="font-semibold">Priyanka Karki</h3>
                            <p class="text-gray-500">Actress</p>
                        </div>
                    </div>
                    <!-- Testimonial 4 -->
                    <div class="swiper-slide">
                        <div class="bg-white p-6 rounded-lg shadow-md text-center mx-4">
                            <img src="./image/client4.jpeg" alt="Client" class="w-20 h-20 rounded-full mx-auto mb-4">
                            <p class="text-gray-600 mb-4">"The plumbing service was top-notch! Quick response and excellent work."</p>
                            <h3 class="font-semibold">Anita Shrestha</h3>
                            <p class="text-gray-500">Business Owner</p>
                        </div>
                    </div>
                    <!-- Testimonial 5 -->
                    <div class="swiper-slide">
                        <div class="bg-white p-6 rounded-lg shadow-md text-center mx-4">
                            <img src="./image/client5.jpeg" alt="Client" class="w-20 h-20 rounded-full mx-auto mb-4">
                            <p class="text-gray-600 mb-4">"I highly recommend their decoration services. My home looks amazing!"</p>
                            <h3 class="font-semibold">Ramesh Gurung</h3>
                            <p class="text-gray-500">Teacher</p>
                        </div>
                    </div>
                </div>
                <!-- Navigation Buttons -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <!-- Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-16 bg-indigo-600 text-white text-center">
        <div class="container mx-auto px-4 animate-fade-in-up">
            <h2 class="text-3xl font-bold mb-4">Ready to Book?</h2>
            <p class="text-lg mb-6">Get started with your service request today!</p>
            <a href="select.php" class="bg-yellow-400 text-black px-6 py-3 rounded-full font-semibold hover:bg-yellow-500 transition transform hover:scale-105">Book Now</a>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="bg-gray-900 text-gray-300 py-8">
        <div class="container mx-auto px-4 text-center">
            <p>© 2025 EasyLiving. All rights reserved.</p>
            <div class="mt-4">
                <a href="about.html" class="hover:text-indigo-400 transition mx-2">About Us</a> |
                <a href="services.html" class="hover:text-indigo-400 transition mx-2">Services</a> |
                <a href="contact.html" class="hover:text-indigo-400 transition mx-2">Contact</a>
            </div>
        </div>
    </footer>

    <!-- JavaScript for Additional Interactivity -->
    <script>
        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add animation on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-fade-in-up').forEach(element => {
            observer.observe(element);
        });

        // Dropdown fix: Keep open on hover
        const dropdown = document.querySelector('.dropdown');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        if (dropdown && dropdownMenu) {
            dropdown.addEventListener('mouseenter', () => {
                dropdownMenu.style.display = 'block';
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.transform = 'translateY(0)';
            });
            dropdown.addEventListener('mouseleave', () => {
                dropdownMenu.style.display = 'none';
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.transform = 'translateY(-10px)';
            });
        }
    </script>
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        // Initialize Swiper for testimonial slider only
        const swiper = new Swiper('.testimonial-slider', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            pagination: {
                el: '.testimonial-slider .swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.testimonial-slider .swiper-button-next',
                prevEl: '.testimonial-slider .swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
        });
    </script>
</body>
</html>
