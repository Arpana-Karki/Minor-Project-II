<?php
// process_payment.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paymentMethod = $_POST['payment_method'];

    // Example Payment Logic
    if ($paymentMethod == "e-seva") {
        // Redirect to E-seva payment gateway page (you would need to integrate the E-seva API here)
        header("Location: e_seva_payment.php");
        exit();
    } elseif ($paymentMethod == "khalti") {
        // Redirect to Khalti payment gateway page (you would need to integrate Khalti API here)
        header("Location: khalti_payment.php");
        exit();
    } elseif ($paymentMethod == "cod") {
        // Process Cash on Delivery (COD)
        // You can update the order status in the database here to mark it as "Pending"
        
        // Example message for COD
        echo "You selected Cash on Delivery. Your order will be processed for delivery soon.";
        
        // You can add the order status to the database
        // Example of inserting order status into the database (pseudo code):
        // $orderStatus = "Pending";
        // Insert into orders table: UPDATE orders SET status='$orderStatus' WHERE user_id='$userId';
    }
}
?>
