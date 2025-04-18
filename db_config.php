<?php
// db_config.php
// Database connection file

// Database configuration
$db_host = 'localhost';
$db_user = 'root';      
$db_password = '';         
$db_name = 'currency_buddy';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>