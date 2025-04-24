<?php
require('connection.php');
session_start();

// Check if the session is active and prompt for confirmation
if (isset($_SESSION['username'])) {
    echo "<script>
            if (confirm('Are you sure you want to logout?')) {
                window.location = 'index.php';
            } else {
                window.location = 'dashboard.php'; // Redirect to a different page if the user cancels
            }
          </script>";
    session_destroy(); // Destroy the session if confirmed
}
?>
