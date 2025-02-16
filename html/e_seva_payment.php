<?php
// e_seva_payment.php

// Sample data, replace with actual API request data
$amount = 1500; // Total amount to be paid
$paymentGatewayUrl = "https://api.e-seva.com/payment"; // Hypothetical E-seva API URL

// Redirect to the E-seva payment gateway with parameters
echo "<form action='{$paymentGatewayUrl}' method='POST'>
    <input type='hidden' name='amount' value='{$amount}'>
    <input type='submit' value='Pay with E-seva'>
</form>";
?>
