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

// Function to get user's budget data
function getUserBudgetData($conn, $user_id) {
    $budget_data = [
        'total_budget' => 1000, // Default value
        'currency' => 'USD',    // Default value
        'categories' => []
    ];
    
    // Check if user_settings table exists
    $result = $conn->query("SHOW TABLES LIKE 'user_settings'");
    $user_settings_exists = $result->num_rows > 0;
    
    // Get budget amount and currency if the table exists
    if ($user_settings_exists) {
        // Get budget amount - check if the prepare statement succeeds
        $query = "SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_name = 'budget_amount'";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $budget_data['total_budget'] = floatval($row['setting_value']);
            }
            $stmt->close();
        } else {
            // Handle error - query could not be prepared
            error_log("Error preparing statement: " . $conn->error);
        }
        
        // Get budget currency - check if the prepare statement succeeds
        $query = "SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_name = 'budget_currency'";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $budget_data['currency'] = $row['setting_value'];
            }
            $stmt->close();
        } else {
            // Handle error - query could not be prepared
            error_log("Error preparing statement: " . $conn->error);
        }
    }
    
    // Get budget categories - check if the prepare statement succeeds
    $query = "SELECT name, allocation_percentage FROM budget_categories WHERE user_id = ? ORDER BY id";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $budget_data['categories'][] = [
                'name' => $row['name'],
                'allocation_percentage' => floatval($row['allocation_percentage'])
            ];
        }
        $stmt->close();
    } else {
        // Handle error - query could not be prepared
        error_log("Error preparing statement: " . $conn->error);
    }
    
    return $budget_data;
}

// Get user's budget data - wrap in try/catch to handle any database errors safely
try {
    $user_id = $_SESSION['user_id'];
    $budget_data = getUserBudgetData($conn, $user_id);
} catch (Exception $e) {
    // Log the error and continue with default values
    error_log("Error fetching budget data: " . $e->getMessage());
    $budget_data = [
        'total_budget' => 1000,
        'currency' => 'USD',
        'categories' => []
    ];
}

// Currency symbols
$currencySymbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'JPY' => '¥',
    'CAD' => '$',
    'AUD' => '$'
];

// Get currency symbol
$currencySymbol = isset($currencySymbols[$budget_data['currency']]) ? $currencySymbols[$budget_data['currency']] : '$';

// Include header file
include 'header.php';
?>


<section class="dashboard-main">
    <div class="container">
        <div class="dashboard-container">
            <div class="dashboard-sidebar">
                <div class="sidebar-header">
                    <h3>My Dashboard</h3>
                    <p>Welcome, <?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'User'; ?></p>
                </div>
                <ul class="sidebar-menu">
                    <li class="active"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="currencyConverter.php"><i class="fas fa-exchange-alt"></i> Currency Converter</a></li>
                    <li><a href="budgetAllocator.php"><i class="fas fa-chart-pie"></i> Budget Allocator</a></li>
                    <li><a href="budgetCalculator.php"><i class="fas fa-calculator"></i> Budget Calculator</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile Settings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            
            <div class="dashboard-content">
                <div class="content-header">
                    <h2>Welcome to Your Financial Dashboard</h2>
                    <p>Track, manage, and optimize your finances in one place</p>
                </div>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-title">Currency Conversions</div>
                        <div class="stat-value">24</div>
                        <div class="stat-description">Last 30 days</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Budget Allocation</div>
                        <div class="stat-value"><?php echo $currencySymbol . number_format($budget_data['total_budget'], 2); ?></div>
                        <div class="stat-description">Monthly budget</div>
                    </div>
                    
                    <?php
                    // Find savings category
                    $savings_amount = 0;
                    $savings_percentage = 0;
                    foreach($budget_data['categories'] as $category) {
                        if(strtolower($category['name']) === 'savings') {
                            $savings_percentage = $category['allocation_percentage'];
                            $savings_amount = ($savings_percentage / 100) * $budget_data['total_budget'];
                            break;
                        }
                    }
                    ?>
                    
                    <div class="stat-card">
                        <div class="stat-title">Savings</div>
                        <div class="stat-value"><?php echo $currencySymbol . number_format($savings_amount, 2); ?></div>
                        <div class="stat-description"><?php echo number_format($savings_percentage, 1); ?>% of budget</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Financial Health</div>
                        <div class="stat-value">Good</div>
                        <div class="stat-description">Based on spending patterns</div>
                    </div>
                </div>
                
                <div class="dashboard-widgets">
                    <div class="widget recent-activity">
                        <h3>Recent Activity</h3>
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>Amount</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Apr 17, 2025</td>
                                    <td>Currency Conversion</td>
                                    <td>USD → EUR</td>
                                    <td><span class="status-complete">Complete</span></td>
                                </tr>
                                <tr>
                                    <td>Apr 15, 2025</td>
                                    <td>Budget Updated</td>
                                    <td><?php echo $currencySymbol . number_format($budget_data['total_budget'], 2); ?></td>
                                    <td><span class="status-complete">Complete</span></td>
                                </tr>
                                <tr>
                                    <td>Apr 10, 2025</td>
                                    <td>Expense Added</td>
                                    <td>$120</td>
                                    <td><span class="status-complete">Complete</span></td>
                                </tr>
                                <tr>
                                    <td>Apr 5, 2025</td>
                                    <td>Savings Goal Set</td>
                                    <td>$5,000</td>
                                    <td><span class="status-processing">In Progress</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="widget budget-overview">
                        <h3>Budget Overview</h3>
                        <div class="budget-categories">
                            <?php
                            // Display top 4 budget categories (excluding savings)
                            $displayedCategories = 0;
                            foreach($budget_data['categories'] as $category) {
                                if(strtolower($category['name']) !== 'savings' && $displayedCategories < 4) {
                                    $displayedCategories++;
                                    $percentage = $category['allocation_percentage'];
                                    $amount = ($percentage / 100) * $budget_data['total_budget'];
                                    $category_limit = ($percentage / 100) * $budget_data['total_budget'];
                                    
                                    // For demo purposes, show a random spent amount between 40% and 95% of category limit
                                    $spent_percentage = mt_rand(40, 95);
                                    $spent_amount = ($spent_percentage / 100) * $category_limit;
                                    $progress_class = $spent_percentage > 90 ? 'warning' : '';
                                    ?>
                                    <div class="budget-category">
                                        <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                                        <div class="category-bar">
                                            <div class="category-progress <?php echo $progress_class; ?>" style="width: <?php echo $spent_percentage; ?>%"></div>
                                        </div>
                                        <div class="category-amount">
                                            <span><?php echo $currencySymbol . number_format($spent_amount, 2); ?></span>
                                            <span class="category-total">/ <?php echo $currencySymbol . number_format($category_limit, 2); ?></span>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            
                            // If we don't have enough categories, add some placeholders
                            while($displayedCategories < 4) {
                                $displayedCategories++;
                                ?>
                                <div class="budget-category">
                                    <div class="category-name">Category <?php echo $displayedCategories; ?></div>
                                    <div class="category-bar">
                                        <div class="category-progress" style="width: 50%"></div>
                                    </div>
                                    <div class="category-amount">
                                        <span><?php echo $currencySymbol; ?>0.00</span>
                                        <span class="category-total">/ <?php echo $currencySymbol; ?>0.00</span>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="budget-actions">
                            <a href="budgetAllocator.php" class="btn btn-secondary btn-sm">Adjust Budget</a>
                            <a href="budgetCalculator.php" class="btn btn-primary btn-sm">Add Expense</a>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="currencyConverter.php" class="quick-action-btn">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Convert Currency</span>
                        </a>
                        <a href="budgetAllocator.php" class="quick-action-btn">
                            <i class="fas fa-chart-pie"></i>
                            <span>Update Budget</span>
                        </a>
                        <a href="budgetCalculator.php" class="quick-action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Expense</span>
                        </a>
                        <a href="reports.php" class="quick-action-btn">
                            <i class="fas fa-chart-line"></i>
                            <span>View Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Footer -->
<footer class="dashboard-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="currencyConverter.php">Currency Converter</a></li>
                    <li><a href="budgetAllocator.php">Budget Allocator</a></li>
                    <li><a href="profile.php">Profile Settings</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="help.php">Help Center</a></li>
                    <li><a href="faq.php">FAQs</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 FinanceBuddy. All rights reserved.</p>
        </div>
    </div>
</footer>
<!-- End Dashboard Footer -->

<?php
// Close database connection
$conn->close();
?>