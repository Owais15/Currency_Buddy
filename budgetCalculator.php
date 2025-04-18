<?php
// budgetCalculator.php
include 'header.php';
?>

<section class="page-header">
    <div class="container">
        <h2>Travel Budget Calculator</h2>
        <p>Plan your trip expenses with our comprehensive budget calculator</p>
    </div>
</section>

<section class="calculator-section">
    <div class="container">
        <div class="calculator-container">
            <div class="currency-selection">
                <div class="form-group">
                    <label for="homeCurrency">Home Currency</label>
                    <select id="homeCurrency" class="form-control">
                        <option value="" selected disabled>Select Currency</option>
                        <!-- Will be populated by JavaScript -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="destCurrency">Destination Currency</label>
                    <select id="destCurrency" class="form-control">
                        <option value="" selected disabled>Select Currency</option>
                        <!-- Will be populated by JavaScript -->
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="tripDuration">Duration (days)</label>
                <div class="duration-input">
                    <input type="number" id="tripDuration" class="form-control" value="10" min="1">
                    <button class="calendar-btn"><i class="fas fa-calendar-alt"></i></button>
                </div>
            </div>

            <div class="currency-radio">
                <p>Enter amounts in:</p>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="entryType" value="home" checked>
                        <span>Home Currency</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="entryType" value="destination">
                        <span>Destination Currency</span>
                    </label>
                </div>
            </div>

            <div class="expense-categories">
                <div class="expense-category" id="accommodation">
                    <div class="category-icon">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <div class="category-name">Accommodation</div>
                    <div class="category-input">
                        <input type="number" class="form-control expense-input" data-category="accommodation" min="0">
                        <span class="per-day">per day</span>
                    </div>
                </div>

                <div class="expense-category" id="food">
                    <div class="category-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="category-name">Food</div>
                    <div class="category-input">
                        <input type="number" class="form-control expense-input" data-category="food" min="0">
                        <span class="per-day">per day</span>
                    </div>
                </div>

                <div class="expense-category" id="transportation">
                    <div class="category-icon">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="category-name">Transportation</div>
                    <div class="category-input">
                        <input type="number" class="form-control expense-input" data-category="transportation" min="0">
                        <span class="per-day">per day</span>
                    </div>
                </div>

                <div class="expense-category" id="activities">
                    <div class="category-icon">
                        <i class="fas fa-hiking"></i>
                    </div>
                    <div class="category-name">Activities</div>
                    <div class="category-input">
                        <input type="number" class="form-control expense-input" data-category="activities" min="0">
                        <span class="per-day">per day</span>
                    </div>
                </div>

                <div class="expense-category" id="miscellaneous">
                    <div class="category-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="category-name">Miscellaneous</div>
                    <div class="category-input">
                        <input type="number" class="form-control expense-input" data-category="miscellaneous" min="0">
                        <span class="per-day">per day</span>
                    </div>
                </div>
            </div>

            <div class="add-category">
                <button id="addCategoryBtn" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Add New Category
                </button>
            </div>

            <div class="calculate-button">
                <button id="calculateBtn" class="btn btn-primary">
                    <i class="fas fa-calculator"></i> Calculate Budget
                </button>
            </div>

            <div class="budget-summary" id="budgetSummary">
                <h3>Budget Summary</h3>
                <div class="total-budget">
                    <div class="label">Total Budget Required</div>
                    <div class="currency-values">
                        <div class="home-currency">
                            <span class="currency-code" id="homeCurrencyCode">-</span>
                            <span class="amount" id="homeCurrencyTotal">0.00</span>
                        </div>
                        <div class="dest-currency">
                            <span class="currency-code" id="destCurrencyCode">-</span>
                            <span class="amount" id="destCurrencyTotal">0.00</span>
                        </div>
                    </div>
                </div>

                <div class="daily-breakdown">
                    <h4>Daily Breakdown</h4>
                    <table id="expenseTable">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th id="homeHeader">-</th>
                                <th id="destHeader">-</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="budgetCalculator.js"></script>
<script src="currencyCodes.js"></script>

<?php
include 'footer.php';
?>