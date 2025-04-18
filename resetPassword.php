<?php
// resetPassword.php
include 'header.php';

// Start session if it hasn't been started in header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error logging for debugging
error_log("Reset password page accessed with token: " . (isset($_GET['token']) ? $_GET['token'] : 'no token'));

// Check if token is provided in the URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['error_message'] = "Invalid or missing reset token.";
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];
$token_valid = false;
$user_id = null;

// Connect to database
require_once 'db_config.php';

// Check if the database connection is successful
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    $_SESSION['error_message'] = "An error occurred. Please try again later.";
    header("Location: login.php");
    exit();
}

// First, check if the password_reset_tokens table exists
$table_check = $conn->query("SHOW TABLES LIKE 'password_reset_tokens'");
if ($table_check->num_rows == 0) {
    // Create the table if it doesn't exist
    $create_table_sql = "CREATE TABLE password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(64) NOT NULL,
        expires DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($create_table_sql)) {
        error_log("Failed to create password_reset_tokens table: " . $conn->error);
        $_SESSION['error_message'] = "An error occurred. Please try again later.";
        header("Location: login.php");
        exit();
    }
}

// Log the token being checked
error_log("Checking token validity for: " . $token);

// Verify token - Debug the query
$check_query = "SELECT user_id, expires FROM password_reset_tokens WHERE token = ?";
$check_stmt = $conn->prepare($check_query);

if (!$check_stmt) {
    error_log("Prepare statement failed: " . $conn->error);
    $_SESSION['error_message'] = "An error occurred. Please try again later.";
    header("Location: login.php");
    exit();
}

$check_stmt->bind_param("s", $token);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

error_log("Token check result rows: " . $check_result->num_rows);

if ($check_result->num_rows > 0) {
    $row = $check_result->fetch_assoc();
    $user_id = $row['user_id'];
    $expires = new DateTime($row['expires']);
    $now = new DateTime();
    
    error_log("Token found for user ID: " . $user_id . ", Expires: " . $row['expires']);
    
    // Check if token has expired
    if ($expires > $now) {
        $token_valid = true;
        error_log("Token is valid and not expired");
    } else {
        error_log("Token has expired. Expiry: " . $expires->format('Y-m-d H:i:s') . ", Now: " . $now->format('Y-m-d H:i:s'));
    }
} else {
    error_log("No token found matching: " . $token);
}

$check_stmt->close();

// If token is invalid, redirect to login page
if (!$token_valid) {
    $_SESSION['error_message'] = "Invalid or expired reset token. Please request a new password reset link.";
    header("Location: login.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_id = $_POST['user_id'];
    $token = $_POST['token'];
    
    $error = false;
    
    // Validate password
    if (strlen($password) < 8) {
        $_SESSION['error_message'] = "Password must be at least 8 characters long.";
        $error = true;
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        $error = true;
    }
    
    // If no errors, update the password
    if (!$error) {
        try {
            // Hash the new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update user's password
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            
            if (!$update_stmt) {
                throw new Exception("Database error in prepare statement: " . $conn->error);
            }
            
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                error_log("Password updated successfully for user ID: " . $user_id);
                
                // Password updated successfully, now delete the used token
                $delete_query = "DELETE FROM password_reset_tokens WHERE token = ?";
                $delete_stmt = $conn->prepare($delete_query);
                
                if ($delete_stmt) {
                    $delete_stmt->bind_param("s", $token);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                    error_log("Reset token deleted after successful password change");
                }
                
                // Set success message and redirect to login
                $_SESSION['success_message'] = "Your password has been reset successfully. You can now log in with your new password.";
                header("Location: login.php");
                exit();
            } else {
                throw new Exception("Failed to update password: " . $update_stmt->error);
            }
            
            $update_stmt->close();
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred while resetting your password. Please try again.";
            error_log("Password reset error: " . $e->getMessage());
        }
    }
}

// Get error message from session if it exists
$error_message = "";
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the error message from session
}
?>

<section class="auth-container">
    <div class="container">
        <div class="auth-form-container">
            <div class="auth-header">
                <h2>Reset Your Password</h2>
                <p>Enter your new password below</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form action="resetPassword.php?token=<?php echo htmlspecialchars($token); ?>" method="post" class="auth-form">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your new password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength-meter">
                        <div class="strength-bar"></div>
                    </div>
                    <small>Use at least 8 characters with letters, numbers, and symbols</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                
                <div class="auth-footer">
                    Remember your password? <a href="login.php">Log In</a>
                </div>
            </form>
        </div>
        <div class="auth-banner">
            <h3>Strong Password Tips</h3>
            <ul class="benefits-list">
                <li><i class="fas fa-check-circle"></i> Use at least 8 characters</li>
                <li><i class="fas fa-check-circle"></i> Include uppercase & lowercase letters</li>
                <li><i class="fas fa-check-circle"></i> Add numbers (0-9)</li>
                <li><i class="fas fa-check-circle"></i> Include special characters (!@#$%)</li>
                <li><i class="fas fa-check-circle"></i> Avoid common words or phrases</li>
                <li><i class="fas fa-check-circle"></i> Don't reuse passwords</li>
            </ul>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthBar = document.querySelector('.strength-bar');
    
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 25;
        
        strengthBar.style.width = strength + '%';
        
        if (strength < 50) {
            strengthBar.style.backgroundColor = '#ff4d4d';
        } else if (strength < 75) {
            strengthBar.style.backgroundColor = '#ffd633';
        } else {
            strengthBar.style.backgroundColor = '#66cc66';
        }
    });
    
    // Password toggle functionality
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            // Toggle the input type between password and text
            if (input.type === 'password') {
                input.type = 'text';
                input.classList.add('password-visible');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                input.classList.remove('password-visible');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>