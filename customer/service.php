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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Gradient Background */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: "Inter", sans-serif;
        }

        /* Navbar Styling */
        .navbar {
            background: #ff6600; /* Solid orange background */
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .nav-link {
            position: relative;
            color: white;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #ffe6cc; /* Light orange hover effect */
        }

        .nav-link::after {
            content: "";
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: #ffe6cc;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Flip Animation for Cards */
        /* @keyframes flipIn {
            0% { transform: rotateY(0deg); opacity: 1; }
            50% { transform: rotateY(90deg); opacity: 0.7; }
            100% { transform: rotateY(0deg); opacity: 1; }
        } */

        .service-card:hover {
            animation: flipIn 0.6s ease-in-out;
            transform: scale(1.0) translateY(-10px);
            box-shadow: 0 12px 30px rgba(255, 102, 0, 0.4);
            border-color:rgb(236, 137, 70);
        }

        /* Glowing Search Bar */
        .search-box:focus {
            box-shadow: 0 0 15px rgba(255, 102, 0, 0.6);
            border-color: #ff6600;
        }

        /* Glowing Button */
        .glow-button {
            position: relative;
            overflow: hidden;
        }

        .glow-button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.4),
                transparent
            );
            transition: 0.5s;
        }

        .glow-button:hover::before {
            left: 100%;
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
    </style>
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="navbar flex justify-between items-center h-16 text-white">
        <div class="flex space-x-8">
            <a href="index.php" class="nav-link text-lg">Home</a>
            <a href="about.html" class="nav-link text-lg">About</a>
            <a href="services.php" class="nav-link text-lg">Services</a>
            <a href="wishlist.html" class="nav-link text-lg">Wishlist</a>
            
        </div>
    </nav>

    <!-- Search Bar -->
    <div class="flex justify-center items-center my-10">
        <form action="services.php" method="GET" class="search-container">
            <input
                type="text"
                name="search"
                id="searchInput"
                class="search-box w-full p-4 pr-12 border-2 border-orange-500 rounded-full text-lg transition-all duration-300 shadow-lg focus:outline-none bg-white/80"
                placeholder="Search for services..."
                value="<?php echo htmlspecialchars($search); ?>"
            />
            <button
                type="submit"
                class="search-button p-2 bg-orange-500 text-white rounded-full text-lg cursor-pointer hover:bg-orange-600 hover:scale-110 transition-all duration-300"
            >
                🔍
            </button>
        </form>
    </div>

    <!-- Services Section -->
    <h2 class="text-4xl font-bold my-10 text-orange-600 text-center">Our Services</h2>
    <div class="flex flex-wrap justify-center gap-8 px-4">
        <?php if (empty($services)): ?>
            <p class="text-lg text-gray-600">No services found. Try adjusting your search.</p>
        <?php else: ?>
            <?php foreach ($services as $service): ?>
                <div class="service-card w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer border border-transparent">
                    <img
                        src="../Uploads/services/<?php echo htmlspecialchars($service['photo']); ?>"
                        alt="<?php echo htmlspecialchars($service['category']); ?>"
                        class="w-full h-52 object-cover rounded-lg"
                    />
                    <h3 class="mt-4 text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($service['category']); ?></h3>
                    <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($service['description'] ?? 'No description available'); ?></p>
                    <button
                        class="glow-button mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors w-full"
                        onclick="window.location.href='book.php?service=<?php echo $service['id']; ?>'"
                    >
                        <?php echo htmlspecialchars($service['category']); ?>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>