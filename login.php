<?php
// login.php
include 'header.php';

// Start session if it hasn't been started in header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'loginProcess.php';
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
                <h2>Welcome Back</h2>
                <p>Log in to continue managing your finances</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="post" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="forgot-password">
                        <a href="forgotPassword.php">Forgot password?</a>
                    </div>
                </div>
                
                <div class="form-group remember-me">
                    <input type="checkbox" id="remember" name="remember" style="width:0px;">
                    <label for="remember">Remember me on this device</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Log In</button>
                
                <div class="social-login">
                    <p>Or log in with</p>
                    <div class="social-buttons">
                        <a href="#" class="social-btn google"><i class="fab fa-google"></i> Google</a>
                        <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i> Facebook</a>
                    </div>
                </div>
                
                <div class="auth-footer">
                    Don't have an account? <a href="signup.php">Sign Up</a>
                </div>
            </form>
        </div>
        <div class="auth-banner">
            <h3>Access Your Financial Tools</h3>
            <ul class="benefits-list">
                <li><i class="fas fa-exchange-alt"></i> Currency Conversion</li>
                <li><i class="fas fa-chart-pie"></i> Budget Allocation</li>
                <li><i class="fas fa-calculator"></i> Budget Calculator</li>
            </ul>
            <div class="app-preview">
                <img src="https://plus.unsplash.com/premium_photo-1661663603858-2df08fe3d65d?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8YnVkZ2V0fGVufDB8fDB8fHww" alt="App preview">
            </div>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>