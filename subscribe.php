<?php
// subscribe.php
// This file handles the newsletter subscription form submission

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$error_message = "";
$success_message = "";

try {
    // Connect to database
    require_once 'db_config.php';
    
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and validate email
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        
        // Basic validation
        if (empty($email)) {
            $error_message = "Email address is required.";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } else {
            // Check if subscribers table exists, create if it doesn't
            $table_check = $conn->query("SHOW TABLES LIKE 'subscribers'");
            if ($table_check->num_rows == 0) {
                $create_table_sql = "CREATE TABLE subscribers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    subscribed_at DATETIME NOT NULL,
                    status ENUM('active', 'unsubscribed') DEFAULT 'active'
                )";
                
                if (!$conn->query($create_table_sql)) {
                    throw new Exception("Failed to create subscribers table: " . $conn->error);
                }
            }
            
            // Check if email already exists
            $check_query = "SELECT id, status FROM subscribers WHERE email = ?";
            $check_stmt = $conn->prepare($check_query);
            
            if ($check_stmt === false) {
                throw new Exception("Database error in prepare statement: " . $conn->error);
            }
            
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $subscriber = $check_result->fetch_assoc();
                
                if ($subscriber['status'] == 'unsubscribed') {
                    // Re-activate unsubscribed email
                    $update_query = "UPDATE subscribers SET status = 'active', subscribed_at = NOW() WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    
                    if ($update_stmt === false) {
                        throw new Exception("Database error in prepare statement: " . $conn->error);
                    }
                    
                    $update_stmt->bind_param("i", $subscriber['id']);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "You have successfully re-subscribed to our newsletter!";
                        sendConfirmationEmail($email);
                    } else {
                        throw new Exception("Re-subscription failed: " . $update_stmt->error);
                    }
                    
                    $update_stmt->close();
                } else {
                    $error_message = "This email is already subscribed to our newsletter.";
                }
            } else {
                // Insert new subscriber into database
                $insert_query = "INSERT INTO subscribers (email, subscribed_at) VALUES (?, NOW())";
                $insert_stmt = $conn->prepare($insert_query);
                
                if ($insert_stmt === false) {
                    throw new Exception("Database error in prepare statement: " . $conn->error);
                }
                
                $insert_stmt->bind_param("s", $email);
                
                if ($insert_stmt->execute()) {
                    // Subscriber added successfully
                    $success_message = "Thank you for subscribing to our newsletter!";
                    
                    // Send confirmation email
                    $send_email_result = sendConfirmationEmail($email);
                    
                    // Log email sending result for debugging
                    error_log("Subscription confirmation email to $email " . ($send_email_result ? "sent successfully" : "failed to send"));
                    
                    if (!$send_email_result) {
                        $success_message .= " However, the confirmation email could not be sent. Please check your email address.";
                    }
                } else {
                    throw new Exception("Subscription failed: " . $insert_stmt->error);
                }
                
                $insert_stmt->close();
            }
            
            $check_stmt->close();
        }
    }
} catch (Exception $e) {
    // Capture any exceptions that occur during processing
    $error_message = "An error occurred: " . $e->getMessage();
    
    // Log this error for administrators
    error_log("Subscription error: " . $e->getMessage());
}

/**
 * Send confirmation email to new subscribers using PHPMailer
 * 
 * @param string $email The subscriber's email address
 * @return bool True if email was sent successfully, false otherwise
 */
function sendConfirmationEmail($email) {
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
        $mail->addAddress($email);                            // Add recipient
        $mail->addReplyTo('buddycurrency@gmail.com', 'Currency Buddy');
        
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = "Thank you for subscribing to Currency Buddy!";
        
        // Email message - HTML format
        $mail->Body = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Thanks for subscribing to Currency Buddy</title>
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
                .newsletter-preview {
                    margin: 20px 0;
                    border: 1px solid #ddd;
                    padding: 15px;
                    background-color: #fff;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Currency Buddy</h1>
            </div>
            <div class="content">
                <h2>Thanks for subscribing!</h2>
                <p>You\'ve successfully subscribed to Currency Buddy\'s newsletter. You\'ll now receive our latest financial tips, currency updates, and exclusive offers.</p>
                
                <div class="newsletter-preview">
                    <h3>What to expect in our newsletter:</h3>
                    <ul>
                        <li>Weekly currency market updates</li>
                        <li>Money-saving financial tips</li>
                        <li>Exclusive tools and resources</li>
                        <li>Special offers and promotions</li>
                    </ul>
                </div>
                
                <p>Want to explore our tools right away?</p>
                <a href="' . $app_url . '/currencyConverter.php" class="button">Try Our Currency Converter</a>
                
                <p>If you have any questions or feedback, please don\'t hesitate to contact our support team.</p>
                
                <p>Best regards,<br>The Currency Buddy Team</p>
            </div>
            <div class="footer">
                <p>This email was sent to ' . htmlspecialchars($email) . ' because you subscribed to Currency Buddy\'s newsletter.</p>
                <p>If you didn\'t subscribe or want to unsubscribe, <a href="' . $app_url . '/unsubscribe.php?email=' . urlencode($email) . '">click here</a>.</p>
                <p>&copy; ' . date('Y') . ' Currency Buddy. All rights reserved.</p>
            </div>
        </body>
        </html>
        ';
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Thanks for subscribing to Currency Buddy!\n\n" .
                         "You've successfully subscribed to Currency Buddy's newsletter. You'll now receive our latest financial tips, currency updates, and exclusive offers.\n\n" .
                         "What to expect in our newsletter:\n" .
                         "- Weekly currency market updates\n" .
                         "- Money-saving financial tips\n" .
                         "- Exclusive tools and resources\n" .
                         "- Special offers and promotions\n\n" .
                         "Want to explore our tools right away? Visit: $app_url/currencyConverter.php\n\n" .
                         "If you have any questions or feedback, please don't hesitate to contact our support team.\n\n" .
                         "Best regards,\nThe Currency Buddy Team\n\n" .
                         "To unsubscribe, visit: $app_url/unsubscribe.php?email=" . urlencode($email);
        
        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log email errors
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Redirect back to the page with appropriate message
if (!empty($error_message) || !empty($success_message)) {
    // Start session to pass messages
    session_start();
    
    if (!empty($error_message)) {
        $_SESSION['subscribe_error'] = $error_message;
    }
    
    if (!empty($success_message)) {
        $_SESSION['subscribe_success'] = $success_message;
    }
    
    // Redirect back to referring page
    $redirect_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header("Location: $redirect_to");
    exit();
}
?>