
<?php
// ------------------------------
// STEP 1: Define Database Constants (for PDO)
// ------------------------------
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'dunzo_db');





// ------------------------------
// STEP 4: Tax Rate
// ------------------------------
$taxRate = 0.09;  // 9% tax rate (written as decimal)

// ------------------------------
// STEP 4: Razorpay API Keys
// (Note: Replace these with your actual keys in real projects)
// ------------------------------
define('RAZORPAY_KEY_ID', 'rzp_test_RIbycxqJ3lfSOe');     // Replace with your actual Key ID from the dashboard
define('RAZORPAY_KEY_SECRET', 'b6ejo634aLc6qYbHZKZGUH3i');  // Replace with your actual Key Secret from the dashboard



// ------------------------------
// STEP 6: Connecting to Database (for mysqli)
//$conn = new mysqli($serverName, $userName, $password, $dbName);
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check if connection failed
if ($conn->connect_error) {
    die("Error: Cannot connect to database. " . $conn->connect_error);
}

// ------------------------------
// STEP 7: Function to Calculate Shipping
// ------------------------------



function calculate_shipping_charge($orderTotal) {
    global $standardShipping, $reducedShipping,
           $reducedShippingLimit, $freeShippingLimit;

    // Free shipping for large orders
    if ($orderTotal >= $freeShippingLimit) {
        return 0;
    } 
    // Reduced shipping for medium orders
    elseif ($orderTotal >= $reducedShippingLimit) {
        return $reducedShipping;
    } 
    // Standard shipping for small orders
    else {
        return $standardShipping;
    }
}


?>
