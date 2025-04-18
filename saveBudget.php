<?php
// Start session to get the user ID
session_start();

// Set error reporting for development environment
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1);     // Enable error logging
ini_set('error_log', 'budget_errors.log'); // Set error log file

// Log function for debugging
function logError($message, $data = null) {
    $log = date('[Y-m-d H:i:s]') . " - " . $message;
    if ($data !== null) {
        $log .= " - Data: " . json_encode($data);
    }
    error_log($log);
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Return error response
    logError("User not logged in. Session data: " . json_encode($_SESSION));
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Include database connection
if (!file_exists('db_config.php')) {
    logError("db_config.php file not found");
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database configuration file not found']);
    exit();
}

require_once 'db_config.php';

// Check if database connection is successful
if ($conn->connect_error) {
    logError("Database connection failed: " . $conn->connect_error);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to database']);
    exit();
}

logError("Database connection successful");

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Get the raw input data for logging
$raw_input = file_get_contents('php://input');
logError("Raw input received", $raw_input);

// Get the data sent from the client
$data = json_decode($raw_input, true);

if (!$data) {
    logError("JSON decode failed: " . json_last_error_msg());
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
    exit();
}

if (!isset($data['categories']) || !isset($data['totalBudget']) || !isset($data['currency'])) {
    logError("Missing required data fields", $data);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Missing required data: categories, totalBudget, or currency']);
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];
$total_budget = floatval($data['totalBudget']);
$currency = $conn->real_escape_string($data['currency']);
$categories = $data['categories'];

logError("Processing budget save for user ID: $user_id, Budget: $total_budget, Currency: $currency", ['categories_count' => count($categories)]);

// Check if users table exists and has the user ID
$user_check_query = "SELECT id FROM users WHERE id = ?";
$stmt = $conn->prepare($user_check_query);
if (!$stmt) {
    logError("Prepare statement failed (user check): " . $conn->error);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    logError("User ID $user_id not found in users table");
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'User not found in database']);
    $stmt->close();
    exit();
}
$stmt->close();

// Start transaction
logError("Starting database transaction");
$conn->begin_transaction();

try {
    // NEW CODE: Check if budget_categories table exists and create it if not
    $table_check_query = "SHOW TABLES LIKE 'budget_categories'";
    $table_check_result = $conn->query($table_check_query);
    
    if ($table_check_result->num_rows === 0) {
        logError("Creating budget_categories table as it doesn't exist");
        $create_table_query = "CREATE TABLE budget_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(50) NOT NULL,
            allocation_percentage DECIMAL(5,2) NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        if (!$conn->query($create_table_query)) {
            throw new Exception("Failed to create budget_categories table: " . $conn->error);
        }
        
        logError("budget_categories table created successfully");
    }

    // First, delete existing budget categories for this user
    $delete_query = 'DELETE FROM budget_categories WHERE user_id = ?';
    $stmt = $conn->prepare($delete_query);
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed (delete categories): " . $conn->error);
    }
    
    $stmt->bind_param('i', $user_id);
    $delete_result = $stmt->execute();
    
    if (!$delete_result) {
        throw new Exception("Failed to delete existing categories: " . $stmt->error);
    }
    
    logError("Deleted existing categories for user $user_id");
    $stmt->close();
    
    // Check if user_settings table exists
    $table_check_result = $conn->query("SHOW TABLES LIKE 'user_settings'");
    
    if (!$table_check_result) {
        throw new Exception("Failed to check if user_settings table exists: " . $conn->error);
    }
    
    if ($table_check_result->num_rows === 0) {
        logError("Creating user_settings table");
        // Create the table if it doesn't exist
        $create_table_query = "CREATE TABLE user_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            setting_name VARCHAR(50) NOT NULL,
            setting_value TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_setting (user_id, setting_name),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $create_result = $conn->query($create_table_query);
        
        if (!$create_result) {
            throw new Exception("Failed to create user_settings table: " . $conn->error);
        }
        
        logError("user_settings table created successfully");
    }
    
    // Insert or update budget amount
    $budget_amount_str = (string)$total_budget;
    $budget_query = "INSERT INTO user_settings (user_id, setting_name, setting_value) 
                    VALUES (?, 'budget_amount', ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?";
    
    $stmt = $conn->prepare($budget_query);
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed (budget amount): " . $conn->error);
    }
    
    $stmt->bind_param('iss', $user_id, $budget_amount_str, $budget_amount_str);
    $budget_result = $stmt->execute();
    
    if (!$budget_result) {
        throw new Exception("Failed to save budget amount: " . $stmt->error);
    }
    
    logError("Saved budget amount: $budget_amount_str");
    $stmt->close();
    
    // Insert or update currency
    $currency_query = "INSERT INTO user_settings (user_id, setting_name, setting_value) 
                      VALUES (?, 'budget_currency', ?) 
                      ON DUPLICATE KEY UPDATE setting_value = ?";
    
    $stmt = $conn->prepare($currency_query);
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed (currency): " . $conn->error);
    }
    
    $stmt->bind_param('iss', $user_id, $currency, $currency);
    $currency_result = $stmt->execute();
    
    if (!$currency_result) {
        throw new Exception("Failed to save currency: " . $stmt->error);
    }
    
    logError("Saved currency: $currency");
    $stmt->close();
    
    // Insert new budget categories
    $category_query = 'INSERT INTO budget_categories (user_id, name, allocation_percentage) VALUES (?, ?, ?)';
    $stmt = $conn->prepare($category_query);
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed (insert categories): " . $conn->error);
    }
    
    foreach ($categories as $index => $category) {
        $name = $conn->real_escape_string($category['name']);
        $percentage = floatval($category['percentage']);
        
        logError("Saving category $index", ["name" => $name, "percentage" => $percentage]);
        
        $stmt->bind_param('isd', $user_id, $name, $percentage);
        $category_result = $stmt->execute();
        
        if (!$category_result) {
            throw new Exception("Failed to save category '$name': " . $stmt->error);
        }
    }
    
    $stmt->close();
    
    // Commit transaction
    $commit_result = $conn->commit();
    
    if (!$commit_result) {
        throw new Exception("Failed to commit transaction: " . $conn->error);
    }
    
    logError("Budget saved successfully for user $user_id");
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Budget saved successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the detailed error
    logError("ERROR: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error: ' . $e->getMessage(),
        'trace' => $e->getTrace()
    ]);
}

// Close connection
$conn->close();
logError("Database connection closed");
?>