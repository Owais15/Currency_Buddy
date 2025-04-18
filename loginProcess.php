<?php
// loginProcess.php - Fixed version

// Start session first, before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Connect to database
require_once 'db_config.php';

// Check if database connection is successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Check if inputs are empty
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Email and password are required.";
        header("Location: login.php");
        exit();
    } else {
        // Check if users table exists, create if it doesn't
        $table_check = $conn->query("SHOW TABLES LIKE 'users'");
        if ($table_check->num_rows == 0) {
            $create_table_sql = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fullname VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL
            )";
            
            if (!$conn->query($create_table_sql)) {
                die("Failed to create users table: " . $conn->error);
            }
        }
        
        // Prepare query to check user credentials
        $query = "SELECT id, fullname, email, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        
        // Check if prepare statement was successful
        if ($stmt === false) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        
        // Bind parameters and execute
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct
                
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['loggedin'] = true;  // Set loggedin to true for header.php
                
                // Set remember me cookie if checked
                if ($remember) {
                    // Check if remember_tokens table exists
                    $token_table_check = $conn->query("SHOW TABLES LIKE 'remember_tokens'");
                    if ($token_table_check->num_rows == 0) {
                        $create_token_table = "CREATE TABLE remember_tokens (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NOT NULL,
                            token VARCHAR(64) NOT NULL,
                            expires DATETIME NOT NULL,
                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                        )";
                        
                        if (!$conn->query($create_token_table)) {
                            // Just log the error but continue without remember me functionality
                            error_log("Failed to create remember_tokens table: " . $conn->error);
                        }
                    }
                    
                    // Generate token
                    $token = bin2hex(random_bytes(32));
                    
                    // Store token in database
                    $token_query = "INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))";
                    $token_stmt = $conn->prepare($token_query);
                    
                    if ($token_stmt === false) {
                        // Log the error but continue without remember me functionality
                        error_log("Prepare failed for token insertion: " . $conn->error);
                    } else {
                        $token_stmt->bind_param("is", $user['id'], $token);
                        $token_stmt->execute();
                        
                        // Set cookie
                        setcookie("remember_me", $token, time() + (86400 * 30), "/"); // 30 days
                    }
                }
                
                // Close statement before redirect
                $stmt->close();
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
                
            } else {
                $_SESSION['error_message'] = "Invalid email or password.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid email or password.";
            header("Location: login.php");
            exit();
        }
        
        // Close statement
        $stmt->close();
    }
}

// If direct access to this file (not through POST), redirect to login page
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}
?>