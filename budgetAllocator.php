<?php
// budgetAllocator.php
include 'header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Budget Allocator</h1>
        <p>Plan and visualize your spending with our interactive budget allocation tool</p>
    </div>
</section>

<section class="budget-allocator">
    <div class="container">
        <div class="budget-card">
            <div class="budget-inputs">
                <div class="input-group">
                    <label for="totalBudget">Total Budget</label>
                    <input type="number" id="totalBudget" class="form-control" placeholder="Enter amount" value="1000">
                </div>
                
                <div class="input-group">
                    <label for="currency">Currency</label>
                    <div class="select-wrapper">
                        <select id="currency" class="form-control">
                            <option value="USD" selected>United States Dollar (USD) $</option>
                            <option value="EUR">Euro (EUR) €</option>
                            <option value="GBP">British Pound (GBP) £</option>
                            <option value="JPY">Japanese Yen (JPY) ¥</option>
                            <option value="CAD">Canadian Dollar (CAD) $</option>
                            <option value="AUD">Australian Dollar (AUD) $</option>
                        </select>
                    </div>
                </div>
                
                <button id="toggleChart" class="btn btn-primary">Hide Chart</button>
            </div>
            
            <div id="chartContainer" class="chart-container">
                <canvas id="budgetChart"></canvas>
            </div>
            
            <div class="budget-categories">
                <h2>Budget Categories</h2>
                
                <div class="category-table">
                    <div class="category-header">
                        <div class="category-col">Category</div>
                        <div class="category-col">Percentage</div>
                        <div class="category-col">Amount</div>
                    </div>
                    
                    <div id="categoriesList">
                        <!-- Categories will be added here by JavaScript -->
                    </div>
                </div>
                
                <div class="budget-summary">
                    <div class="summary-item">
                        <span>Total Allocated:</span>
                        <span id="totalAllocated">100.00%</span>
                        <span id="totalAllocatedAmount">$0.00</span>
                    </div>
                    <div class="summary-item">
                        <span>Remaining:</span>
                        <span id="remainingPercentage">0.00%</span>
                        <span id="remainingAmount">$1000.00</span>
                    </div>
                </div>
                
                <div class="budget-actions">
                    <button id="addCategory" class="btn btn-secondary">Add Category</button>
                    <button id="saveBudget" class="btn btn-primary">Save Budget</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="budgetAllocator.js"></script>

<?php
include 'footer.php';
?>