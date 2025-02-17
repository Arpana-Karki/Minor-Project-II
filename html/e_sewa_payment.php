<?php
// e_sewa_payment.php

// Sample data, replace with actual API request data
$amount = 1500; // Total amount to be paid
$paymentGatewayUrl = "https://api.e-sewa.com/payment"; // Hypothetical E-sewa API URL

// Redirect to the E-sewa payment gateway with parameters
echo "<form action='{$paymentGatewayUrl}' method='POST'>
    <input type='hidden' name='amount' value='{$amount}'>
    <input type='submit' value='Pay with E-sewa'>
</form>";
?>
