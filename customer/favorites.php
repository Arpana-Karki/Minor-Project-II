<?php
session_start();
include('../db_connection.php');

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// Initialize session favorites
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

// Handle remove from favorites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $subservice_id = (int)$_POST['remove_favorite'];
    if ($user_id) {
        $delete_query = "DELETE FROM favorites WHERE user_id = ? AND subservice_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'ii', $user_id, $subservice_id);
        mysqli_stmt_execute($delete_stmt);
        mysqli_stmt_close($delete_stmt);
        $message = "Removed from Favorites!";
    } else {
        $_SESSION['favorites'] = array_diff($_SESSION['favorites'], [$subservice_id]);
        $message = "Removed from Favorites!";
    }
}

// Fetch favorites
$favorites = [];
if ($user_id) {
    $query = "
        SELECT ss.id, ss.subservice_name, ss.staff_name, ss.price_per_hour, ss.photo, s.category AS parent_category
        FROM favorites f
        JOIN subservices ss ON f.subservice_id = ss.id
        JOIN services s ON ss.parent_service_id = s.id
        WHERE f.user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $favorites[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    if (!empty($_SESSION['favorites'])) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['favorites']), '?'));
        $query = "
            SELECT ss.id, ss.subservice_name, ss.staff_name, ss.price_per_hour, ss.photo, s.category AS parent_category
            FROM subservices ss
            JOIN services s ON ss.parent_service_id = s.id
            WHERE ss.id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, str_repeat('i', count($_SESSION['favorites'])), ...$_SESSION['favorites']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $favorites[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

$message = isset($message) ? $message : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - Easy Living</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: "Inter", sans-serif;
        }
        .navbar {
            background: #ff6600;
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
            color: #ffe6cc;
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
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
        }
        .glow-button:hover::before {
            left: 100%;
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
            <a href="favorites.php" class="nav-link text-lg">Favorites</a>
            <a href="my_bookings.php" class="nav-link text-lg">My Bookings</a>
        </div>
    </nav>

    <!-- Favorites Section -->
    <h2 class="text-4xl font-bold my-10 text-orange-600 text-center">My Favorites</h2>
    <div class="flex flex-wrap justify-center gap-8 px-4">
        <?php if (empty($favorites)): ?>
            <p class="text-lg text-gray-600">No favorites added yet.</p>
        <?php else: ?>
            <?php foreach ($favorites as $favorite): ?>
                <div class="w-72 bg-white/90 p-5 rounded-xl shadow-lg border border-transparent">
                    <img
                        src="../Uploads/services/<?php echo htmlspecialchars($favorite['photo']); ?>"
                        alt="<?php echo htmlspecialchars($favorite['subservice_name']); ?>"
                        class="w-full h-52 object-cover rounded-lg"
                    />
                    <h3 class="mt-4 text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($favorite['subservice_name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Service:</strong> <?php echo htmlspecialchars($favorite['parent_category']); ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Staff:</strong>
                        <a href="staff_profile.php?staff=<?php echo urlencode($favorite['staff_name']); ?>" class="text-orange-600 underline">
                            <?php echo htmlspecialchars($favorite['staff_name']); ?>
                        </a>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Price:</strong> Rs <?php echo number_format($favorite['price_per_hour'], 2); ?>/hour
                    </p>
                    <div class="flex justify-between mt-4">
                        <form method="POST" action="favorites.php" class="inline">
                            <input type="hidden" name="remove_favorite" value="<?php echo $favorite['id']; ?>">
                            <button type="submit" class="glow-button text-sm font-semibold bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600">
                                Remove
                            </button>
                        </form>
                        <button
                            class="glow-button text-sm font-semibold bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-600"
                            onclick="window.location.href='subservice.php#subservice-<?php echo $favorite['id']; ?>'"
                        >
                            Book Now
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Display message
        <?php if ($message): ?>
            alert("<?php echo addslashes($message); ?>");
        <?php endif; ?>
    </script>
</body>
</html>