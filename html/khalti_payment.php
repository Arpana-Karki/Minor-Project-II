<?php
// khalti_payment.php

// Sample data, replace with actual API request data
$amount = 1500; // Total amount to be paid
$paymentGatewayUrl = "https://www.khalti.com/payment"; // Hypothetical Khalti API URL

// Redirect to Khalti payment gateway with parameters
echo "<form action='{$paymentGatewayUrl}' method='POST'>
    <input type='hidden' name='amount' value='{$amount}'>
    <input type='submit' value='Pay with Khalti'>
</form>";
?>
