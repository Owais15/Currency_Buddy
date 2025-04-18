<?php
// logout.php - Handles user logout with confirmation

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Check if confirmation is submitted
if (isset($_POST['confirm_logout']) && $_POST['confirm_logout'] === 'yes') {
    // User confirmed logout - proceed with logout process
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Include header
include 'header.php';
?>

<section class="auth-container">
    <div class="container">
        <div class="auth-form-container">
            <div class="auth-header">
                <h2>Log Out</h2>
                <p>Are you sure you want to log out?</p>
            </div>
            
            <form action="logout.php" method="post" class="auth-form">
                <div class="form-group logout-buttons">
                    <button type="submit" name="confirm_logout" value="yes" class="btn btn-primary">Yes, Log Out</button>
                    <a href="dashboard.php" class="btn btn-secondary">No, Cancel</a>
                </div>
            </form>
        </div>
        <div class="auth-banner">
            <h3>Thank You for Using Currency Buddy</h3>
            <ul class="benefits-list">
                <li><i class="fas fa-exchange-alt"></i> Currency Conversion</li>
                <li><i class="fas fa-chart-pie"></i> Budget Allocation</li>
                <li><i class="fas fa-calculator"></i> Budget Calculator</li>
            </ul>
            <div class="app-preview">
                <img src="https://previews.123rf.com/images/gfxnazim/gfxnazim2306/gfxnazim230600046/206344983-financial-planning-and-management-illustration-vector-set-professional-finance-management-visual.jpg" alt="App preview">
            </div>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>