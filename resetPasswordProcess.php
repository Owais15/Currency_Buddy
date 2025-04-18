<?php
// resetPasswordProcess.php
// This file handles the password reset request submission

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include PHPMailer classes
require_once './libs/PHPMailer/src/Exception.php';
require_once './libs/PHPMailer/src/PHPMailer.php';
require_once './libs/PHPMailer/src/SMTP.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        
        // Basic validation
        if (empty($email)) {
            $_SESSION['reset_message'] = "Email address is required.";
            $_SESSION['reset_message_type'] = "danger";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['reset_message'] = "Please enter a valid email address.";
            $_SESSION['reset_message_type'] = "danger";
        } else {
            // Check if email exists in the database
            $check_query = "SELECT id, fullname FROM users WHERE email = ?";
            $check_stmt = $conn->prepare($check_query);
            
            if ($check_stmt === false) {
                throw new Exception("Database error in prepare statement: " . $conn->error);
            }
            
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                // For security reasons, don't reveal if email exists or not
                $_SESSION['reset_message'] = "If your email address exists in our database, you will receive a password recovery link shortly.";
                $_SESSION['reset_message_type'] = "success";
            } else {
                // Fetch user information
                $user = $check_result->fetch_assoc();
                $user_id = $user['id'];
                $fullname = $user['fullname'];
                
                // Generate a unique token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Create password_reset_tokens table if it doesn't exist
                $table_check = $conn->query("SHOW TABLES LIKE 'password_reset_tokens'");
                if ($table_check->num_rows == 0) {
                    $create_table_sql = "CREATE TABLE password_reset_tokens (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        token VARCHAR(64) NOT NULL,
                        expires DATETIME NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    )";
                    
                    if (!$conn->query($create_table_sql)) {
                        throw new Exception("Failed to create password_reset_tokens table: " . $conn->error);
                    }
                }
                
                // First, delete any existing reset tokens for this user
                $delete_query = "DELETE FROM password_reset_tokens WHERE user_id = ?";
                $delete_stmt = $conn->prepare($delete_query);
                
                if ($delete_stmt === false) {
                    throw new Exception("Database error in prepare statement: " . $conn->error);
                }
                
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
                
                // Insert the token into the database
                $insert_query = "INSERT INTO password_reset_tokens (user_id, token, expires) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                
                if ($insert_stmt === false) {
                    throw new Exception("Database error in prepare statement: " . $conn->error);
                }
                
                $insert_stmt->bind_param("iss", $user_id, $token, $expires);
                
                if ($insert_stmt->execute()) {
                    // Token stored successfully
                    
                    // Send reset email
                    $send_email_result = sendResetEmail($fullname, $email, $token);
                    
                    // Log email sending result for debugging
                    error_log("Reset email to $email " . ($send_email_result ? "sent successfully" : "failed to send"));
                    
                    $_SESSION['reset_message'] = "If your email address exists in our database, you will receive a password recovery link shortly.";
                    $_SESSION['reset_message_type'] = "success";
                    
                    if (!$send_email_result) {
                        // Log the error but don't inform the user (for security)
                        error_log("Failed to send password reset email to $email");
                    }
                } else {
                    throw new Exception("Failed to store reset token: " . $insert_stmt->error);
                }
            }
            
            // Close statement
            $check_stmt->close();
        }
    }
} catch (Exception $e) {
    // Capture any exceptions that occur during processing
    $_SESSION['reset_message'] = "An error occurred. Please try again later.";
    $_SESSION['reset_message_type'] = "danger";
    
    // You might want to log this error for administrators
    error_log("Password reset error: " . $e->getMessage());
}

// Redirect back to the forgotPassword page
header("Location: forgotPassword.php");
exit();

/**
 * Send password reset email to user using PHPMailer
 * 
 * @param string $fullname The user's full name
 * @param string $email The user's email address
 * @param string $token The password reset token
 * @return bool True if email was sent successfully, false otherwise
 */
function sendResetEmail($fullname, $email, $token) {
    // App details
    $app_name = "Currency Buddy";
    $app_email = "buddycurrency@gmail.com";
    $app_url = "http://localhost/Project_CC/"; // Update this to your local URL
    
    // Reset link
    $reset_link = $app_url . "resetPassword.php?token=" . $token;
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings for Gmail
        $mail->isSMTP();                                      // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                 // Gmail SMTP server
        $mail->SMTPAuth   = true;                             // Enable SMTP authentication
        $mail->Username   = 'buddycurrency@gmail.com';        // Your Gmail address
        $mail->Password   = 'mmxfksljjfognkka';               // Use the same password as in signupProcess.php
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption
        $mail->Port       = 587;                              // Gmail SMTP port for TLS
        
        // Debug settings - comment out after fixing the issue
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                // Enable verbose debug output
        $mail->Debugoutput = 'error_log';                     // Log to PHP error log
        
        // Recipients
        $mail->setFrom('buddycurrency@gmail.com', 'Currency Buddy');
        $mail->addAddress($email, $fullname);                 // Add recipient
        $mail->addReplyTo('buddycurrency@gmail.com', 'Currency Buddy');
        
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = "Password Reset Request - Currency Buddy";
        
        // Email message - HTML format
        $mail->Body = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Password Reset - Currency Buddy</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                }
                .header {
                    background-color: #4a90e2;
                    padding: 20px;
                    text-align: center;
                    color: white;
                }
                .content {
                    padding: 20px;
                    background-color: #f9f9f9;
                }
                .footer {
                    text-align: center;
                    padding: 10px;
                    font-size: 12px;
                    color: #666;
                }
                .button {
                    display: inline-block;
                    background-color: #4a90e2;
                    color: white;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 4px;
                    margin: 20px 0;
                }
                .warning {
                    background-color: #fff8e1;
                    border-left: 4px solid #ffc107;
                    padding: 10px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Currency Buddy</h1>
            </div>
            <div class="content">
                <h2>Password Reset Request</h2>
                <p>Hello, ' . htmlspecialchars($fullname) . '!</p>
                <p>We received a request to reset your password for your Currency Buddy account. If you did not make this request, you can safely ignore this email.</p>
                
                <p>To reset your password, click the button below:</p>
                <div style="text-align: center;">
                    <a href="' . $reset_link . '" class="button">Reset My Password</a>
                </div>
                
                <div class="warning">
                    <p><strong>Note:</strong> This link will expire in 1 hour for security reasons.</p>
                </div>
                
                <p>If the button above doesn\'t work, you can copy and paste the following link into your browser:</p>
                <p style="word-break: break-all;">' . $reset_link . '</p>
                
                <p>If you need any assistance, please contact our support team.</p>
                
                <p>Best regards,<br>The Currency Buddy Team</p>
            </div>
            <div class="footer">
                <p>This email was sent to ' . htmlspecialchars($email) . ' because a password reset was requested for your Currency Buddy account.</p>
                <p>&copy; ' . date('Y') . ' Currency Buddy. All rights reserved.</p>
            </div>
        </body>
        </html>
        ';
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Password Reset Request - Currency Buddy\n\n" .
                         "Hello, $fullname!\n\n" .
                         "We received a request to reset your password for your Currency Buddy account. If you did not make this request, you can safely ignore this email.\n\n" .
                         "To reset your password, please visit the following link:\n" .
                         "$reset_link\n\n" .
                         "Note: This link will expire in 1 hour for security reasons.\n\n" .
                         "If you need any assistance, please contact our support team.\n\n" .
                         "Best regards,\nThe Currency Buddy Team";
        
        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log email errors
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>