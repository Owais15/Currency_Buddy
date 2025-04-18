<?php
// currencyConverter.php


// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'header.php';
include 'db_config.php';


// Initialize variables
$amount = 1;
$from_currency = isset($_SESSION['default_currency']) ? $_SESSION['default_currency'] : 'USD';
$to_currency = 'EUR';
$result = '';
$error = '';
$conversion_rate = 0;

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']) && $_SESSION['loggedin'] === true;

// Load currency data from JSON file
$currencies_json = file_get_contents('currencies.json');
$currencies = json_decode($currencies_json, true);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['convert'])) {
    $amount = floatval($_POST['amount']);
    $from_currency = $_POST['from_currency'];
    $to_currency = $_POST['to_currency'];

    // Make API call using file_get_contents() instead of cURL (to simplify)
    $api_key = '99a0aec004527b1a155623da';
    $api_url = "https://v6.exchangerate-api.com/v6/{$api_key}/latest/{$from_currency}";

    // Fetch the exchange rate data using file_get_contents
    $response = file_get_contents($api_url);

    if ($response !== false) {
        $data = json_decode($response, true);
        
        if ($data['result'] == 'success') {
            $conversion_rate = $data['conversion_rates'][$to_currency];
            $result = $amount * $conversion_rate;

            // Save to favorites if user is logged in and checkbox is checked
            if ($logged_in && isset($_POST['save_favorite']) && $_POST['save_favorite'] == 1) {
                $user_id = $_SESSION['user_id']; // Changed from $_SESSION['id'] to $_SESSION['user_id']
                
                // Check if favorites table exists
                $table_check = $conn->query("SHOW TABLES LIKE 'currency_favorites'");
                if ($table_check->num_rows == 0) {
                    // Create table if it doesn't exist
                    $create_table = "CREATE TABLE currency_favorites (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        currency_code VARCHAR(10) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY (user_id, currency_code)
                    )";
                    $conn->query($create_table);
                }
                
                // Check if already favorited
                $check_sql = "SELECT * FROM currency_favorites WHERE user_id = ? AND currency_code = ?";
                $check_stmt = $conn->prepare($check_sql);
                
                if ($check_stmt) {
                    $check_stmt->bind_param("is", $user_id, $to_currency);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows == 0) {
                        // Add to favorites
                        $insert_sql = "INSERT INTO currency_favorites (user_id, currency_code) VALUES (?, ?)";
                        $insert_stmt = $conn->prepare($insert_sql);
                        if ($insert_stmt) {
                            $insert_stmt->bind_param("is", $user_id, $to_currency);
                            $insert_stmt->execute();
                            $insert_stmt->close();
                        }
                    }
                    $check_stmt->close();
                }
            }
        } else {
            $error = "Error fetching exchange rates. Please try again.";
        }
    } else {
        $error = "Error fetching exchange rates. Please check your internet connection and try again.";
    }
}

// Get user's favorite currencies
$favorites = [];
if ($logged_in) {
    $user_id = $_SESSION['user_id']; // Changed from $_SESSION['id'] to $_SESSION['user_id']
    
    // Check if favorites table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'currency_favorites'");
    if ($table_check->num_rows > 0) {
        $favorites_sql = "SELECT currency_code FROM currency_favorites WHERE user_id = ?";
        $favorites_stmt = $conn->prepare($favorites_sql);
        
        if ($favorites_stmt) {
            $favorites_stmt->bind_param("i", $user_id);
            $favorites_stmt->execute();
            $favorites_result = $favorites_stmt->get_result();
            
            while ($row = $favorites_result->fetch_assoc()) {
                $favorites[] = $row['currency_code'];
            }
            $favorites_stmt->close();
        }
    }
}

// Fallback if currencies.json isn't available
if (empty($currencies)) {
    $currencies = [
        'USD' => ['name' => 'US Dollar', 'country_code' => 'US'],
        'EUR' => ['name' => 'Euro', 'country_code' => 'EU'],
        'GBP' => ['name' => 'British Pound', 'country_code' => 'GB'],
        'JPY' => ['name' => 'Japanese Yen', 'country_code' => 'JP'],
        'CAD' => ['name' => 'Canadian Dollar', 'country_code' => 'CA'],
        'AUD' => ['name' => 'Australian Dollar', 'country_code' => 'AU']
    ];
}
?>

<section class="page-header">
    <div class="container">
        <h1>Currency Converter</h1>
        <p>Convert between 160+ currencies with real-time exchange rates</p>
    </div>
</section>

<section class="currency-converter">
    <div class="container">
        <div class="converter-wrapper">
            <div class="converter-card">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="converter-form">
                    <div class="amount-input">
                        <label for="amount">Amount</label>
                        <input type="number" id="amount" name="amount" value="<?php echo $amount; ?>" min="0.01" step="0.01" required>
                    </div>
                    
                    <div class="currency-selectors">
                        <div class="currency-select">
                            <label for="from_currency">From</label>
                            <div class="select-with-flag">
                                <img id="fromFlag" class="currency-flag" src="" alt="Flag">
                                <select id="from_currency" name="from_currency" required>
                                    <?php foreach ($currencies as $code => $currency): ?>
                                    <option value="<?php echo $code; ?>" <?php echo ($from_currency == $code) ? 'selected' : ''; ?> 
                                            data-country="<?php echo $currency['country_code']; ?>">
                                        <?php echo $code . ' - ' . $currency['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <button type="button" id="swap-currencies" class="swap-btn">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        
                        <div class="currency-select">
                            <label for="to_currency">To</label>
                            <div class="select-with-flag">
                                <img id="toFlag" class="currency-flag" src="" alt="Flag">
                                <select id="to_currency" name="to_currency" required>
                                    <?php foreach ($currencies as $code => $currency): ?>
                                    <option value="<?php echo $code; ?>" <?php echo ($to_currency == $code) ? 'selected' : ''; ?>
                                            data-country="<?php echo $currency['country_code']; ?>">
                                        <?php echo $code . ' - ' . $currency['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($logged_in): ?>
                    <div class="save-favorite">
                        <input type="checkbox" id="save_favorite" name="save_favorite" value="1">
                        <label for="save_favorite">Save this currency to favorites</label>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="convert" class="btn btn-primary convert-btn">Convert</button>
                </form>
                
                <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($result): ?>
                <div class="conversion-result">
                    <h3>Conversion Result</h3>
                    <div class="result-box">
                        <div class="result-amount">
                            <span class="amount"><?php echo number_format($amount, 2); ?></span>
                            <span class="currency"><?php echo $from_currency; ?></span>
                        </div>
                        <div class="result-equals">=</div>
                        <div class="result-amount">
                            <span class="amount"><?php echo number_format($result, 2); ?></span>
                            <span class="currency"><?php echo $to_currency; ?></span>
                        </div>
                    </div>
                    <div class="exchange-rate">
                        <p>Exchange Rate: 1 <?php echo $from_currency; ?> = <?php echo number_format($conversion_rate, 6); ?> <?php echo $to_currency; ?></p>
                        <p class="update-time">Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($logged_in && !empty($favorites)): ?>
            <div class="favorites-section">
                <h3>Your Favorite Currencies</h3>
                <div class="favorites-list">
                    <?php foreach ($favorites as $favorite_code): ?>
                    <?php if (isset($currencies[$favorite_code])): ?>
                    <div class="favorite-currency" data-code="<?php echo $favorite_code; ?>">
                        <img src="https://flagsapi.com/<?php echo $currencies[$favorite_code]['country_code']; ?>/flat/32.png" alt="<?php echo $favorite_code; ?> flag">
                        <span><?php echo $favorite_code; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="converter-info">
    <div class="container">
        <div class="info-cards">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Real-Time Rates</h3>
                <p>Our currency converter uses up-to-the-minute exchange rates to ensure accuracy.</p>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3>160+ Currencies</h3>
                <p>Convert between major and exotic currencies from around the world.</p>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Save Favorites</h3>
                <p>Create an account to save your frequently used currencies for quick access.</p>
            </div>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>

<script src="currencyConverter.js"></script>
<script>
// Simple fallback for currencies.js if it's missing
document.addEventListener('DOMContentLoaded', function() {
    // Update flag images when page loads and when currency selections change
    function updateFlags() {
        const fromCurrency = document.getElementById('from_currency');
        const toCurrency = document.getElementById('to_currency');
        const fromFlag = document.getElementById('fromFlag');
        const toFlag = document.getElementById('toFlag');
        
        if (fromCurrency && fromFlag) {
            const fromCountry = fromCurrency.options[fromCurrency.selectedIndex].getAttribute('data-country');
            fromFlag.src = `https://flagsapi.com/${fromCountry}/flat/32.png`;
        }
        
        if (toCurrency && toFlag) {
            const toCountry = toCurrency.options[toCurrency.selectedIndex].getAttribute('data-country');
            toFlag.src = `https://flagsapi.com/${toCountry}/flat/32.png`;
        }
    }
    
    // Swap currencies function
    document.getElementById('swap-currencies').addEventListener('click', function() {
        const fromCurrency = document.getElementById('from_currency');
        const toCurrency = document.getElementById('to_currency');
        const tempValue = fromCurrency.value;
        
        fromCurrency.value = toCurrency.value;
        toCurrency.value = tempValue;
        
        updateFlags();
    });
    
    // Update flags when currency selections change
    document.getElementById('from_currency').addEventListener('change', updateFlags);
    document.getElementById('to_currency').addEventListener('change', updateFlags);
    
    // Initialize flags on page load
    updateFlags();
    
    // Make favorite currencies clickable
    const favoriteCurrencies = document.querySelectorAll('.favorite-currency');
    favoriteCurrencies.forEach(function(currency) {
        currency.addEventListener('click', function() {
            document.getElementById('to_currency').value = this.getAttribute('data-code');
            updateFlags();
        });
    });
});
</script>