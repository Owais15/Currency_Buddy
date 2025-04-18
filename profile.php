<?php
// Start session before anything else
session_start();

// Check if user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id']) || !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db_config.php';

// Initialize variables
$success_message = '';
$error_message = '';

// Get user data from database
$user_id = $_SESSION['user_id'];

// Debug information
$debug_info = '';
$debug_info .= "User ID: " . $user_id . "<br>";

// First, let's check if the user exists with a simple query
$check_user = $conn->query("SELECT * FROM users WHERE id = $user_id");
if (!$check_user) {
    $error_message = "Database error checking user: " . $conn->error;
    $debug_info .= "Query error: " . $conn->error . "<br>";
} else {
    if ($check_user->num_rows === 0) {
        $error_message = "User not found in database.";
        $debug_info .= "User not found with ID: $user_id<br>";
    } else {
        $user_data = $check_user->fetch_assoc();
        $debug_info .= "User found. Username: " . ($user_data['username'] ?? 'N/A') . "<br>";
        
        // Debug: Show available columns
        $debug_info .= "Available columns: ";
        foreach ($user_data as $column => $value) {
            $debug_info .= "$column, ";
        }
        $debug_info .= "<br>";
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Build the SQL query based on the columns that exist
    $update_query = "UPDATE users SET ";
    $update_parts = [];
    $update_values = [];
    $update_types = "";
    
    // Check which fields we should update based on what we have
    if (array_key_exists('fullname', $user_data)) {
        $update_parts[] = "fullname = ?";
        $update_values[] = $fullname;
        $update_types .= "s";
    }
    
    if (array_key_exists('email', $user_data)) {
        $update_parts[] = "email = ?";
        $update_values[] = $email;
        $update_types .= "s";
    }
    
    if (isset($_POST['phone']) && array_key_exists('phone', $user_data)) {
        $phone = trim($_POST['phone']);
        $update_parts[] = "phone = ?";
        $update_values[] = $phone;
        $update_types .= "s";
    }
    
    if (isset($_POST['currency_preference']) && array_key_exists('currency_preference', $user_data)) {
        $currency_preference = trim($_POST['currency_preference']);
        $update_parts[] = "currency_preference = ?";
        $update_values[] = $currency_preference;
        $update_types .= "s";
    }
    
    if (!empty($update_parts)) {
        $update_query .= implode(", ", $update_parts) . " WHERE id = ?";
        $update_values[] = $user_id;
        $update_types .= "i";
        
        $update_stmt = $conn->prepare($update_query);
        
        if ($update_stmt) {
            // Dynamically bind parameters
            $params = array_merge([$update_types], $update_values);
            $refs = [];
            foreach($params as $key => $value) {
                $refs[$key] = &$params[$key];
            }
            call_user_func_array([$update_stmt, 'bind_param'], $refs);
            
            if ($update_stmt->execute()) {
                // Update session data
                if (isset($fullname) && !empty($fullname)) {
                    $_SESSION['fullname'] = $fullname;
                }
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Error updating profile: " . $update_stmt->error;
            }
            
            $update_stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
    
    // Handle password change if provided
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } else {
            // Check if the password column exists
            if (array_key_exists('password', $user_data)) {
                // Verify current password - this assumes password is stored as hash
                if (password_verify($current_password, $user_data['password'])) {
                    // Hash new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password in database
                    $password_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    
                    if ($password_update) {
                        $password_update->bind_param("si", $hashed_password, $user_id);
                        
                        if ($password_update->execute()) {
                            $success_message = "Profile and password updated successfully!";
                        } else {
                            $error_message = "Error updating password: " . $password_update->error;
                        }
                        
                        $password_update->close();
                    } else {
                        $error_message = "Error preparing password statement: " . $conn->error;
                    }
                } else {
                    $error_message = "Current password is incorrect.";
                }
            } else {
                $error_message = "Password field not found in database.";
            }
        }
    }
}

// Include header file
include 'header.php';
?>

<section class="profile-section">
    <div class="container">
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="sidebar-header">
                    <h3>My Profile</h3>
                    <p>Manage your account settings</p>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="active"><a href="profile.php"><i class="fas fa-user"></i> Profile Settings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            
            <div class="profile-content">
                <div class="content-header">
                    <h2>Profile Settings</h2>
                    <p>Update your personal information and preferences</p>
                </div>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['debug']) && $_GET['debug'] == 1): ?>
                <div class="debug-info" style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px;">
                    <h4>Debug Information</h4>
                    <?php echo $debug_info; ?>
                </div>
                <?php endif; ?>
                
                <div class="profile-form-container">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="profile-form">
                        <div class="form-section">
                            <h3>Personal Information</h3>
                            
                            <?php if (array_key_exists('username', $user_data ?? [])): ?>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" disabled class="form-control">
                                <small class="form-text">Username cannot be changed</small>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (array_key_exists('fullname', $user_data ?? [])): ?>
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user_data['fullname'] ?? ''); ?>" class="form-control" required>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (array_key_exists('email', $user_data ?? [])): ?>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" class="form-control" required>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (array_key_exists('phone', $user_data ?? [])): ?>
                            <div class="form-group">
                                <label for="phone">Phone Number (Optional)</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" class="form-control">
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (array_key_exists('currency_preference', $user_data ?? [])): ?>
                        <div class="form-section">
                            <h3>Preferences</h3>
                            
                            <div class="form-group">
                                <label for="currency_preference">Preferred Currency</label>
                                <select id="currency_preference" name="currency_preference" class="form-control">
                                    <option value="USD" <?php echo (($user_data['currency_preference'] ?? '') === 'USD') ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                    <option value="EUR" <?php echo (($user_data['currency_preference'] ?? '') === 'EUR') ? 'selected' : ''; ?>>Euro (EUR)</option>
                                    <option value="GBP" <?php echo (($user_data['currency_preference'] ?? '') === 'GBP') ? 'selected' : ''; ?>>British Pound (GBP)</option>
                                    <option value="JPY" <?php echo (($user_data['currency_preference'] ?? '') === 'JPY') ? 'selected' : ''; ?>>Japanese Yen (JPY)</option>
                                    <option value="CAD" <?php echo (($user_data['currency_preference'] ?? '') === 'CAD') ? 'selected' : ''; ?>>Canadian Dollar (CAD)</option>
                                    <option value="AUD" <?php echo (($user_data['currency_preference'] ?? '') === 'AUD') ? 'selected' : ''; ?>>Australian Dollar (AUD)</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (array_key_exists('password', $user_data ?? [])): ?>
                        <div class="form-section">
                            <h3>Change Password</h3>
                            <p class="section-note">Leave blank if you don't want to change your password</p>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control">
                                <small class="form-text">Minimum 8 characters, include numbers and special characters for better security</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'footer.php';
?>