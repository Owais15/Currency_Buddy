<?php
// index.php
include 'header.php';
?>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Manage Your Money With Confidence</h2>
                <p>Convert currencies and track your budget all in one place</p>
                <div class="hero-buttons">
                    <a href="currencyConverter.php" class="btn btn-primary">Convert Currency</a>
                    <a href="budgetAllocator.php" class="btn btn-secondary">Plan Your Budget</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://previews.123rf.com/images/gfxnazim/gfxnazim2306/gfxnazim230600046/206344983-financial-planning-and-management-illustration-vector-set-professional-finance-management-visual.jpg" alt="Financial planning illustration">
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2>Features That Make Money Management Simple</h2>
            <div class="feature-cards">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3>Currency Conversion</h3>
                    <p>Convert between 160+ global currencies with real-time exchange rates</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>Budget Allocation</h3>
                    <p>Divide your income into spending categories with smart recommendations</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h3>Budget Calculator</h3>
                    <p>Track expenses and see where your money goes with visual analytics</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <h2>Ready to Take Control of Your Finances?</h2>
            <p>Sign up for free and start your journey to financial wellness today</p>
            <a href="signup.php" class="btn btn-primary">Get Started</a>
        </div>
    </section>

<?php
include 'footer.php';
?>