<?php
// header.php
// Only start session if one isn't already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Buddy - Your Financial Companion</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="currencyConverter.css">
    <link rel="stylesheet" href="budgetAllocator.css">
    <link rel="stylesheet" href="budgetCalculator.css">
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="dashboardStyles.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-coins"></i>
                <h1>Currency Buddy</h1>
            </div>
            <nav>
                <input type="checkbox" id="nav-toggle" class="nav-toggle">
                <label for="nav-toggle" class="nav-toggle-label">
                    <span></span>
                </label>
                <ul>
                    <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="currencyConverter.php" class="<?php echo $current_page == 'currencyConverter.php' ? 'active' : ''; ?>">Currency Converter</a></li>
                    <li><a href="budgetAllocator.php" class="<?php echo $current_page == 'budgetAllocator.php' ? 'active' : ''; ?>">Budget Allocator</a></li>
                    <li><a href="budgetCalculator.php" class="<?php echo $current_page == 'budgetCalculator.php' ? 'active' : ''; ?>">Budget Calculator</a></li>
                    <?php if($logged_in): ?>
                        <li><a href="dashboard.php" class="btn-login"><?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'User'; ?></a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-login">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>