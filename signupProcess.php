<?php
// signupProcess.php
// This file handles the signup form submission

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$error_message = "";
$signup_success = false;

try {
    // Connect to database
    require_once 'db_config.php';
    
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and validate form input
        $fullname = trim(filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $terms = isset($_POST['terms']) ? true : false;
        
        // Basic validation
        if (empty($fullname) || empty($email) || empty($password)) {
            $error_message = "All fields are required.";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } else if (strlen($password) < 8) {
            $error_message = "Password must be at least 8 characters long.";
        } else if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $error_message = "Password must include at least one uppercase letter, one number, and one special character.";
        } else if ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else if (!$terms) {
            $error_message = "You must agree to the Terms of Service.";
        } else {
            // Check if users table exists, create if it doesn't
            $table_check = $conn->query("SHOW TABLES LIKE 'users'");
            if ($table_check->num_rows == 0) {
                $create_table_sql = "CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fullname VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL
                )";
                
                if (!$conn->query($create_table_sql)) {
                    throw new Exception("Failed to create users table: " . $conn->error);
                }
            }
            
            // Check if email already exists
            $check_query = "SELECT id FROM users WHERE email = ?";
            $check_stmt = $conn->prepare($check_query);
            
            if ($check_stmt === false) {
                throw new Exception("Database error in prepare statement: " . $conn->error);
            }
            
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_message = "This email is already registered. Please use a different email or login.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user into database
                $insert_query = "INSERT INTO users (fullname, email, password, created_at) VALUES (?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_query);
                
                if ($insert_stmt === false) {
                    throw new Exception("Database error in prepare statement: " . $conn->error);
                }
                
                $insert_stmt->bind_param("sss", $fullname, $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    // User registered successfully
                    $signup_success = true;
                    
                    // Send welcome email
                    $send_email_result = sendWelcomeEmail($fullname, $email);
                    
                    // Log email sending result for debugging
                    error_log("Welcome email to $email " . ($send_email_result ? "sent successfully" : "failed to send"));
                    
                    if (!$send_email_result) {
                        // Store a message in session to inform user about email status
                        session_start();
                        $_SESSION['email_status'] = "Account created successfully but welcome email could not be sent. Please check your email settings.";
                    }
                    
                    // Start session if not already started
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    $_SESSION['user_id'] = $insert_stmt->insert_id;
                    $_SESSION['fullname'] = $fullname;
                    $_SESSION['email'] = $email;
                    
                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    throw new Exception("Registration failed: " . $insert_stmt->error);
                }
            }
            
            // Close statement
            $check_stmt->close();
        }
    }
} catch (Exception $e) {
    // Capture any exceptions that occur during processing
    $error_message = "An error occurred: " . $e->getMessage();
    
    // You might want to log this error for administrators
    error_log("Signup error: " . $e->getMessage());
}

/**
 * Send welcome email to newly registered users using PHPMailer
 * 
 * @param string $fullname The user's full name
 * @param string $email The user's email address
 * @return bool True if email was sent successfully, false otherwise
 */
function sendWelcomeEmail($fullname, $email) {
    // Manual include of PHPMailer classes
    require_once './libs/PHPMailer/src/Exception.php';
    require_once './libs/PHPMailer/src/PHPMailer.php';
    require_once './libs/PHPMailer/src/SMTP.php';
    
    // App details
    $app_name = "Currency Buddy";
    $app_email = "buddycurrency@gmail.com";
    $app_url = "http://localhost/Project_CC/"; // Update this to your local URL
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings for Gmail
        $mail->isSMTP();                                      // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                 // Gmail SMTP server
        $mail->SMTPAuth   = true;                             // Enable SMTP authentication
        $mail->Username   = 'buddycurrency@gmail.com';        // Your Gmail address
        $mail->Password   = 'mmxfksljjfognkka';
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
        $mail->Subject = "Welcome to Currency Buddy!";
        
        // Email message - HTML format
        $mail->Body = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome to Currency Buddy</title>
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
                .benefits {
                    margin: 20px 0;
                }
                .benefits li {
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Currency Buddy</h1>
            </div>
            <div class="content">
                <h2>Welcome, ' . htmlspecialchars($fullname) . '!</h2>
                <p>Thank you for joining Currency Buddy. We\'re excited to help you manage your currencies!</p>
                
                <p>With your new account, you can:</p>
                <ul class="benefits">
                    <li>Track exchange rates in real-time</li>
                    <li>Convert between multiple currencies</li>
                    <li>Set up alerts for rate changes</li>
                    <li>Make smarter currency decisions</li>
                </ul>
                
                <p>Ready to get started?</p>
                <a href="' . $app_url . '/dashboard.php" class="button">Go to My Dashboard</a>
                
                <p>If you have any questions or need assistance, please don\'t hesitate to contact our support team.</p>
                
                <p>Best regards,<br>The Currency Buddy Team</p>
            </div>
            <div class="footer">
                <p>This email was sent to ' . htmlspecialchars($email) . ' because you signed up for Currency Buddy.</p>
                <p>&copy; ' . date('Y') . ' Currency Buddy. All rights reserved.</p>
            </div>
        </body>
        </html>
        ';
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Welcome to Currency Buddy, $fullname!\n\n" .
                         "Thank you for joining Currency Buddy. We're excited to help you manage your currencies!\n\n" .
                         "With your new account, you can:\n" .
                         "- Track exchange rates in real-time\n" .
                         "- Convert between multiple currencies\n" .
                         "- Set up alerts for rate changes\n" .
                         "- Make smarter currency decisions\n\n" .
                         "Ready to get started? Visit: $app_url/dashboard.php\n\n" .
                         "If you have any questions or need assistance, please don't hesitate to contact our support team.\n\n" .
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

// Display any error or success messages that might be helpful for debugging
if (!empty($error_message)) {
    echo '<div style="color: red; padding: 10px; margin: 10px 0; border: 1px solid red;">';
    echo $error_message;
    echo '</div>';
}
?>