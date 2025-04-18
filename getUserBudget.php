<?php
// Start session to get the user ID
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Include database connection
require_once 'db_config.php';

// Get user ID from session
$user_id = $_SESSION['user_id'];

try {
    // Check if user_settings table exists
    $result = $conn->query("SHOW TABLES LIKE 'user_settings'");
    $user_settings_exists = $result->num_rows > 0;
    
    $budget_data = [
        'total_budget' => 1000, // Default value
        'currency' => 'USD',    // Default value
        'categories' => []
    ];
    
    // Get budget amount and currency if the table exists
    if ($user_settings_exists) {
        // Get budget amount
        $stmt = $conn->prepare("SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_name = 'budget_amount'");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $budget_data['total_budget'] = floatval($row['setting_value']);
        }
        $stmt->close();
        
        // Get budget currency
        $stmt = $conn->prepare("SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_name = 'budget_currency'");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $budget_data['currency'] = $row['setting_value'];
        }
        $stmt->close();
    }
    
    // Get budget categories
    $stmt = $conn->prepare("SELECT name, allocation_percentage FROM budget_categories WHERE user_id = ? ORDER BY name");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $budget_data['categories'][] = [
            'name' => $row['name'],
            'allocation_percentage' => floatval($row['allocation_percentage'])
        ];
    }
    $stmt->close();
    
    // Return success with budget data
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'budget' => $budget_data
    ]);
    
} catch (Exception $e) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

// Close connection
$conn->close();
?>