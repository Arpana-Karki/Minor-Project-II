<?php
session_start();
include('../db_connection.php');

// Handle search query
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$services_query = "SELECT id, category, description, photo FROM services";
if (!empty($search)) {
    $services_query .= " WHERE category LIKE '%$search%'";
}
$services_query .= " ORDER BY category";
$services_result = mysqli_query($conn, $services_query);
$services = [];
while ($service = mysqli_fetch_assoc($services_result)) {
    $services[] = $service;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Easy Living</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Gradient Background */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: "Inter", sans-serif;
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

        /* Service Card Styling */
        .service-card {
            width: 320px;
            background: #ffffff;
            padding: 16px;
            border-radius: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: none;
        }
        .service-card a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .service-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0;
        }
        .service-card h3 {
            margin-top: 12px;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }
        .service-card p {
            margin-top: 8px;
            font-size: 0.875rem;
            color: #6b7280;
            line-height: 1.5;
        }

        /* Glowing Search Bar */
        .search-box:focus {
            box-shadow: 0 0 15px rgba(79, 70, 229, 0.6);
            border-color: #4f46e5;
        }

        /* Search Bar Container */
        .search-container {
            position: relative;
            width: 100%;
            max-width: 33rem; /* Matches w-4/5 max-w-lg */
        }

        .search-button {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Responsive Design for Navbar */
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
        }
    </style>
</head>
<body class="min-h-screen">
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

    <!-- Search Bar -->
    <div class="flex justify-center items-center my-10">
        <form action="services.php" method="GET" class="search-container">
            <input
                type="text"
                name="search"
                id="searchInput"
                class="search-box w-full p-4 pr-12 border-2 border-indigo-600 rounded-full text-lg transition-all duration-300 shadow-lg focus:outline-none bg-white/80"
                placeholder="Search for services..."
                value="<?php echo htmlspecialchars($search); ?>"
            />
            <button
                type="submit"
                class="search-button p-2 bg-indigo-600 text-white rounded-full text-lg cursor-pointer hover:bg-indigo-700 hover:scale-110 transition-all duration-300"
            >
                🔍
            </button>
        </form>
    </div>

    <!-- Services Section -->
    <h2 class="text-4xl font-bold my-10 text-indigo-600 text-center">Our Services</h2>
    <div class="flex flex-wrap justify-center gap-8 px-4">
        <?php if (empty($services)): ?>
            <p class="text-lg text-gray-600">No services found. Try adjusting your search.</p>
        <?php else: ?>
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <a href="./customer/subservice.php">
                        <img
                            src="../Uploads/services/<?php echo htmlspecialchars($service['photo']); ?>"
                            alt="<?php echo htmlspecialchars($service['category']); ?>"
                            class="w-full h-52 object-cover"
                        />
                        <h3 class="mt-4 text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($service['category']); ?></h3>
                        <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($service['description'] ?? 'No description available'); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.navbar ul');
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }
    </script>
</body>
</html>