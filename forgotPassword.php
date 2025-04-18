<?php
// forgotPassword.php
include 'header.php';

// Start session if it hasn't been started in header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'resetPasswordProcess.php';
}

// Get message from session if it exists
$message = "";
$message_type = "";

if (isset($_SESSION['reset_message'])) {
    $message = $_SESSION['reset_message'];
    $message_type = isset($_SESSION['reset_message_type']) ? $_SESSION['reset_message_type'] : 'danger';
    unset($_SESSION['reset_message']); // Clear the message from session
    unset($_SESSION['reset_message_type']); // Clear the message type from session
}
?>

<section class="auth-container">
    <div class="container">
        <div class="auth-form-container">
            <div class="auth-header">
                <h2>Forgot Your Password?</h2>
                <p>Enter your email address to receive a password reset link</p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form action="forgotPassword.php" method="post" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                
                <div class="auth-footer">
                    Remembered your password? <a href="login.php">Log In</a>
                </div>
            </form>
        </div>
        <div class="auth-banner">
            <h3>Password Recovery</h3>
            <ul class="benefits-list">
                <li><i class="fas fa-key"></i> Secure password reset</li>
                <li><i class="fas fa-shield-alt"></i> Data protection</li>
                <li><i class="fas fa-envelope"></i> Email verification</li>
            </ul>
            <div class="password-recovery-info">
                <h4>How it works:</h4>
                <ol>
                    <li>Enter your registered email address</li>
                    <li>We'll send you a password reset link</li>
                    <li>Follow the link to create a new password</li>
                    <li>Log in with your new credentials</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>