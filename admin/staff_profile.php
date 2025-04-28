<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Include database connection
try {
    include('../db_connection.php');
} catch (Exception $e) {
    die("Failed to include db_connection.php: " . htmlspecialchars($e->getMessage()));
}

// Check if customer is logged in
$customer_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;
if ($customer_id === 0) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: ../customer_login.php");
    exit();
}

// Get staff ID from URL
$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch staff details
$staff = null;
$error = null;
if ($staff_id > 0) {
    try {
        $query = "SELECT name, email, phone_number, joined_date, photo, description FROM staff WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $staff_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $staff = $row;
        }
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        $error = "Failed to fetch staff details: " . htmlspecialchars($e->getMessage());
        error_log("Staff query error: " . $e->getMessage());
    }
}

// Fetch assigned subservices
$subservices = [];
if ($staff_id > 0) {
    try {
        $query = "
            SELECT s.subservice_name
            FROM staff_subservices ss
            JOIN subservices s ON ss.subservice_id = s.id
            WHERE ss.staff_id = ?
            ORDER BY s.subservice_name";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $staff_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $subservices[] = $row['subservice_name'];
        }
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        $error = "Failed to fetch subservices: " . htmlspecialchars($e->getMessage());
        error_log("Subservices query error: " . $e->getMessage());
    }
}

// Handle review submission
$review_message = null;
$review_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review_text = !empty($_POST['review']) ? trim($_POST['review']) : null;
    
    // Validate input
    if ($rating < 1 || $rating > 5) {
        $review_error = "Please select a valid rating (1–5 stars).";
    } elseif ($review_text && strlen($review_text) > 500) {
        $review_error = "Review text must be 500 characters or less.";
    } else {
        try {
            // Check for existing review
            $check_query = "SELECT id FROM staff_reviews WHERE staff_id = ? AND customer_id = ?";
            $check_stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($check_stmt, 'ii', $staff_id, $customer_id);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $review_error = "You have already reviewed this staff member.";
            } else {
                // Insert review
                $insert_query = "INSERT INTO staff_reviews (staff_id, customer_id, rating, review) VALUES (?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($insert_stmt, 'iiis', $staff_id, $customer_id, $rating, $review_text);
                mysqli_stmt_execute($insert_stmt);
                mysqli_stmt_close($insert_stmt);
                $review_message = "Review submitted successfully!";
            }
            mysqli_stmt_close($check_stmt);
        } catch (Exception $e) {
            $review_error = "Failed to submit review: " . htmlspecialchars($e->getMessage());
            error_log("Review submission error: " . $e->getMessage());
        }
    }
}

// Fetch reviews and average rating
$reviews = [];
$average_rating = 0;
$total_reviews = 0;
if ($staff_id > 0) {
    try {
        $query = "
            SELECT r.rating, r.review, r.created_at, c.name AS customer_name
            FROM staff_reviews r
            LEFT JOIN customers c ON r.customer_id = c.id
            WHERE r.staff_id = ?
            ORDER BY r.created_at DESC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $staff_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = $row;
        }
        mysqli_stmt_close($stmt);

        // Calculate average rating
        if (!empty($reviews)) {
            $total_rating = array_sum(array_column($reviews, 'rating'));
            $total_reviews = count($reviews);
            $average_rating = round($total_rating / $total_reviews, 1);
        }
    } catch (Exception $e) {
        $error = "Failed to fetch reviews: " . htmlspecialchars($e->getMessage());
        error_log("Reviews query error: " . $e->getMessage());
    }
}

// Set error if no staff found
if (!$staff && $staff_id > 0) {
    $error = "No staff found with the provided ID.";
} elseif ($staff_id === 0) {
    $error = "Invalid staff profile.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile - Easy Living</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: "Inter", sans-serif;
        }
        .navbar {
            background: #ff6600;
            padding: 0.75rem 1.5rem;
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
        .profile-container {
            max-width: 48rem;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #ff6600;
        }
        .subservices-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .subservices-list li {
            background: #f3f4f6;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            color: #333;
        }
        .review-form {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-top: 2rem;
        }
        .review-form input,
        .review-form textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            box-sizing: border-box;
        }
        .review-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #333;
            font-size: 0.875rem;
        }
        .review-form button {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 0.875rem;
        }
        .review-form .submit-btn {
            background: #ff6600;
            color: white;
            border: none;
        }
        .review-form .submit-btn:hover {
            background: #e65c00;
        }
        .star-rating {
            display: flex;
            gap: 0.25rem;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            font-size: 1.5rem;
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ff6600;
        }
        .reviews-list {
            margin-top: 2rem;
        }
        .review-item {
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 0;
        }
        .review-item:last-child {
            border-bottom: none;
        }
        .rating-stars {
            color: #ff6600;
            font-size: 1rem;
        }
        .error-message,
        .success-message {
            font-size: 0.875rem;
            text-align: center;
            margin: 1rem 0;
        }
        .error-message {
            color: #dc2626;
        }
        .success-message {
            color: #16a34a;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="navbar flex justify-between items-center h-12 text-white">
        <div class="flex space-x-6">
            <a href="../index.php" class="nav-link text-base">Home</a>
            <a href="services.php" class="nav-link text-base">Services</a>
            <a href="../about.php" class="nav-link text-base">About</a>
            <a href="favorites.php" class="nav-link text-base">Favorites</a>
            <a href="../profile.php" class="nav-link text-base">My Profile</a>
            <a href="my_bookings.php" class="nav-link text-base">My Bookings</a>
            <a href="../customer_logout.php" class="nav-link text-base">Logout</a>
        </div>
    </nav>

    <!-- Staff Profile Section -->
    <div class="profile-container">
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($staff): ?>
            <div class="flex flex-col items-center md:flex-row md:items-start gap-6">
                <!-- Staff photo -->
                <img
                    src="../Uploads/staff/<?php echo htmlspecialchars($staff['photo'] ?: 'default.jpg'); ?>"
                    alt="<?php echo htmlspecialchars($staff['name']); ?>"
                    class="profile-photo"
                    onerror="this.src='../Uploads/staff/default.jpg'"
                />
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-orange-600"><?php echo htmlspecialchars($staff['name']); ?></h2>
                    <p class="text-gray-600 mt-2">
                        <strong>Email:</strong> <?php echo htmlspecialchars($staff['email'] ?: 'Not provided'); ?>
                    </p>
                    <p class="text-gray-600 mt-1">
                        <strong>Phone:</strong> <?php echo htmlspecialchars($staff['phone_number'] ?: 'Not provided'); ?>
                    </p>
                    <p class="text-gray-600 mt-1">
                        <strong>Joined:</strong> <?php echo htmlspecialchars($staff['joined_date'] ? date('F j, Y', strtotime($staff['joined_date'])) : 'Not provided'); ?>
                    </p>
                    <p class="text-gray-600 mt-1">
                        <strong>Description:</strong> <?php echo htmlspecialchars($staff['description'] ?: 'No description available.'); ?>
                    </p>
                    <!-- Assigned Subservices -->
                    <div class="mt-4">
                        <h3 class="text-lg font-semibold text-gray-800">Assigned Subservices</h3>
                        <?php if (empty($subservices)): ?>
                            <p class="text-gray-600 mt-1">No subservices assigned.</p>
                        <?php else: ?>
                            <ul class="subservices-list">
                                <?php foreach ($subservices as $subservice): ?>
                                    <li><?php echo htmlspecialchars($subservice); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Review Form -->
            <div class="review-form">
                <h3 class="text-lg font-semibold text-orange-600 mb-3">Rate and Review</h3>
                <?php if ($review_message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($review_message); ?></div>
                <?php endif; ?>
                <?php if ($review_error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($review_error); ?></div>
                <?php endif; ?>
                <form method="POST" action="staff_profile.php?id=<?php echo $staff_id; ?>">
                    <div class="star-rating">
                        <input type="radio" name="rating" id="star5" value="5" required />
                        <label for="star5" class="fa fa-star"></label>
                        <input type="radio" name="rating" id="star4" value="4" />
                        <label for="star4" class="fa fa-star"></label>
                        <input type="radio" name="rating" id="star3" value="3" />
                        <label for="star3" class="fa fa-star"></label>
                        <input type="radio" name="rating" id="star2" value="2" />
                        <label for="star2" class="fa fa-star"></label>
                        <input type="radio" name="rating" id="star1" value="1" />
                        <label for="star1" class="fa fa-star"></label>
                    </div>
                    <div class="mt-3">
                        <label for="review">Review (Optional)</label>
                        <textarea id="review" name="review" placeholder="Share your experience (max 500 characters)" rows="4" maxlength="500"></textarea>
                    </div>
                    <div class="button-group mt-3">
                        <button type="submit" name="submit_review" class="submit-btn">Submit Review</button>
                    </div>
                </form>
            </div>
            <!-- Reviews and Average Rating -->
            <div class="reviews-list">
                <h3 class="text-lg font-semibold text-orange-600 mb-3">
                    Reviews (<?php echo $total_reviews; ?>) 
                    <?php if ($total_reviews > 0): ?>
                        - Average: <?php echo $average_rating; ?>/5
                    <?php endif; ?>
                </h3>
                <?php if (empty($reviews)): ?>
                    <p class="text-gray-600">No reviews yet.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="flex items-center gap-2">
                                <div class="rating-stars">
                                    <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                        <i class="fa fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-sm text-gray-600">
                                    by <?php echo htmlspecialchars($review['customer_name'] ?: 'Anonymous'); ?>
                                    on <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                </span>
                            </div>
                            <?php if ($review['review']): ?>
                                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($review['review']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="error-message">Invalid staff profile.</div>
        <?php endif; ?>
    </div>

    <script>
        // Display review messages
        <?php if ($review_message): ?>
            alert("<?php echo addslashes($review_message); ?>");
        <?php endif; ?>
    </script>
</body>
</html>