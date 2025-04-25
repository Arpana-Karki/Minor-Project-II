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
        /* Custom Glassmorphism */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: "";
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #ff6600;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            color: #ff6600;
        }

        /* Gradient Background */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: "Inter", sans-serif;
        }

        /* Enhanced Animations */
        @keyframes flipIn {
            0% { transform: rotateY(0deg); opacity: 1; }
            50% { transform: rotateY(90deg); opacity: 0.7; }
            100% { transform: rotateY(0deg); opacity: 1; }
        }

        .service-card:hover {
            animation: flipIn 0.6s ease-in-out;
            transform: scale(1.05) translateY(-10px);
            box-shadow: 0 12px 30px rgba(255, 102, 0, 0.4), 0 0 20px rgba(255, 102, 0, 0.2);
            border-color: #ff6600;
        }

        .search-box:focus {
            box-shadow: 0 0 15px rgba(255, 102, 0, 0.6);
            border-color: #ff6600;
        }

        /* Parallax Tilt Effect */
        .tilt {
            transform-style: preserve-3d;
            transition: transform 0.3s ease;
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

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="min-h-screen text-center">
    <!-- Navbar -->
    <nav class="navbar glassmorphism p-4 flex justify-between items-center h-16 text-white">
        <div class="flex space-x-8">
            <a href="index.php" class="nav-link font-semibold text-lg">Home</a>
            <a href="about.html" class="nav-link font-semibold text-lg">About</a>
            <a href="services.php" class="nav-link font-semibold text-lg">Services</a>
            <a href="wishlist.html" class="nav-link font-semibold text-lg">Wishlist</a>
            <a href="cart.html" class="nav-link font-semibold text-lg">Add to Cart</a>
        </div>
    </nav>

    <!-- Search Bar -->
    <div class="flex justify-center items-center my-10 relative">
        <form action="services.php" method="GET" class="w-full max-w-lg flex items-center">
            <input
                type="text"
                name="search"
                id="searchInput"
                class="search-box w-full p-4 border-2 border-orange-500 rounded-full text-lg transition-all duration-300 shadow-lg focus:outline-none focus:w-full focus:shadow-xl bg-white/80"
                placeholder="Search for services..."
                value="<?php echo htmlspecialchars($search); ?>"
            />
            <button
                type="submit"
                class="absolute right-4 p-2 bg-orange-500 text-white rounded-full text-lg cursor-pointer hover:bg-orange-600 hover:scale-110 transition-all duration-300"
            >
                🔍
            </button>
        </form>
    </div>

    <!-- Packages & Offers Section -->
    <div class="flex justify-center gap-10 my-16">
        <div
            class="promo-card tilt relative w-96 h-72 rounded-2xl overflow-hidden shadow-2xl cursor-pointer glassmorphism"
            onclick="window.location.href='packages.html'"
        >
            <img
                src="./image/package.jpeg"
                alt="Packages"
                class="w-full h-full object-cover transition-opacity duration-300 hover:opacity-60"
            />
            <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white"
            >
                <h3 class="text-3xl font-bold mb-3">Exclusive Packages</h3>
                <p class="text-base mb-5">Get the best service bundles at great prices!</p>
                <button
                    class="glow-button bg-orange-500 text-white border-none px-5 py-2 font-semibold rounded-lg hover:bg-orange-600 transition-colors"
                >
                    View Packages
                </button>
            </div>
        </div>
        <div
            class="promo-card tilt relative w-96 h-72 rounded-2xl overflow-hidden shadow-2xl cursor-pointer glassmorphism"
            onclick="window.location.href='offers.html'"
        >
            <img
                src="./image/offer.jpeg"
                alt="Offers"
                class="w-full h-full object-cover transition-opacity duration-300 hover:opacity-60"
            />
            <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white"
            >
                <h3 class="text-3xl font-bold mb-3">Special Offers</h3>
                <p class="text-base mb-5">Limited-time discounts on premium services!</p>
                <button
                    class="glow-button bg-orange-500 text-white border-none px-5 py-2 font-semibold rounded-lg hover:bg-orange-600 transition-colors"
                >
                    View Offers
                </button>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <h2 class="text-4xl font-bold my-10 text-orange-600">Our Services</h2>
    <div class="service-list flex flex-wrap justify-center gap-8 px-4" id="serviceContainer">
        <?php if (empty($services)): ?>
            <p class="text-lg text-gray-600">No services found. Try adjusting your search.</p>
        <?php else: ?>
            <?php foreach ($services as $service): ?>
                <div
                    class="service-card tilt w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer border border-transparent"
                >
                    <img
                        src="../Uploads/services/<?php echo htmlspecialchars($service['photo']); ?>"
                        alt="<?php echo htmlspecialchars($service['category']); ?>"
                        class="w-full h-52 object-cover rounded-lg"
                    />
                    <h3 class="mt-4 text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($service['category']); ?></h3>
                    <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($service['description'] ?? 'No description available'); ?></p>
                    <button
                        class="glow-button mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
                        onclick="window.location.href='book.php?service=<?php echo $service['id']; ?>'"
                    >
                        Book Now
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Parallax Tilt Effect for Cards
        document.querySelectorAll(".tilt").forEach((card) => {
            card.addEventListener("mousemove", (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const tiltX = (y - centerY) / 20;
                const tiltY = -(x - centerX) / 20;
                card.style.transform = `rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(1.05)`;
            });

            card.addEventListener("mouseleave", () => {
                card.style.transform = "rotateX(0deg) rotateY(0deg) scale(1)";
            });
        });
    </script>
</body>
</html>