<?php
// resetPasswordComplete.php
// This file handles the password reset form submission

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Connect to database
    require_once 'db_config.php';
    
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and validate form input
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        $token = trim($_POST['token']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Basic validation
        if (empty($password) || empty($confirm_password)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else if (strlen($password) < 8) {
            $_SESSION['error_message'] = "Password must be at least 8 characters long.";
        } else if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $_SESSION['error_message'] = "Password must include at least one uppercase letter, one number, and one special character.";
        } else if ($password !== $confirm_password) {
            $_SESSION['error_message'] = "Passwords do not match.";
        } else {
            // Verify token is still valid
            $check_query = "SELECT id FROM password_reset_tokens WHERE token = ? AND user_id = ? AND expires > NOW()";
            $check_stmt = $conn->prepare($check_query);
            
            if ($check_stmt === false) {
                throw new Exception("Database error in prepare statement: " . $conn->error);
            }
            
            $check_stmt->bind_param("si", $token, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                $_SESSION['error_message'] = "Invalid or expired reset token. Please request a new password reset link.";
                header("Location: forgotPassword.php");
                exit();
            }
            
            // Hash the new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update the user's password
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            
            if ($update_stmt === false) {
                throw new Exception("Database error in prepare statement: " . $conn->error);
            }
            
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                // Password updated successfully
                
                // Delete the used token
                $delete_query = "DELETE FROM password_reset_tokens WHERE user_id = ?";
                $delete_stmt = $conn->prepare($delete_query);
                
                if ($delete_stmt) {
                    $delete_stmt->bind_param("i", $user_id);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }
                
                // Set success message
                $_SESSION['success_message'] = "Your password has been reset successfully. You can now log in with your new password.";
                
                // Redirect to login page
                header("Location: login.php");
                exit();
            } else {
                throw new Exception("Failed to update password: " . $update_stmt->error);
            }
            
            // Close statements
            $check_stmt->close();
            $update_stmt->close();
        }
    }
} catch (Exception $e) {
    // Capture any exceptions that occur during processing
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    
    // You might want to log this error for administrators
    error_log("Password reset error: " . $e->getMessage());
}

// Redirect back to the reset password page with the token
if (isset($token)) {
    header("Location: resetPassword.php?token=" . urlencode($token));
} else {
    header("Location: forgotPassword.php");
}
exit();
?>