document.addEventListener('DOMContentLoaded', function() {
    // Initialize default categories
    const defaultCategories = [
        { id: 1, name: 'Accommodation', color: '#3498db', percentage: 25 },
        { id: 2, name: 'Food', color: '#e74c3c', percentage: 25 },
        { id: 3, name: 'Transport', color: '#2ecc71', percentage: 25 },
        { id: 4, name: 'Miscellaneous', color: '#f1c40f', percentage: 15 },
        { id: 5, name: 'Savings', color: '#9b59b6', percentage: 10 } // Add default Savings category
    ];
    
    // State management
    let budgetState = {
        totalBudget: 1000,
        currency: 'USD',
        categories: [...defaultCategories],
        nextId: 6, // Incremented to account for the new Savings category
        errorPopupActive: false, // Track if error popup is already showing
        savingsCategory: 5 // Track the ID of the Savings category
    };
    
    // Currency symbols
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'JPY': '¥',
        'CAD': '$',
        'AUD': '$'
    };
    
    // Initialize Chart
    let budgetChart;
    
    // DOM Elements
    const totalBudgetInput = document.getElementById('totalBudget');
    const currencySelect = document.getElementById('currency');
    const categoriesList = document.getElementById('categoriesList');
    const totalAllocated = document.getElementById('totalAllocated');
    const totalAllocatedAmount = document.getElementById('totalAllocatedAmount');
    const remainingPercentage = document.getElementById('remainingPercentage');
    const remainingAmount = document.getElementById('remainingAmount');
    const addCategoryBtn = document.getElementById('addCategory');
    const saveBudgetBtn = document.getElementById('saveBudget');
    const toggleChartBtn = document.getElementById('toggleChart');
    const chartContainer = document.getElementById('chartContainer');
    
    // Initialize the application
    function initialize() {
        // Load user's budget from server if available
        loadUserBudget();
        
        initializeChart();
        renderCategories();
        updateBudgetSummary();
        setupEventListeners();
    }
    
    // Load user's budget from the server
    function loadUserBudget() {
        fetch('getUserBudget.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update budget state with user's saved budget
                    if (data.budget) {
                        budgetState.totalBudget = parseFloat(data.budget.total_budget) || 1000;
                        budgetState.currency = data.budget.currency || 'USD';
                        
                        // Update form fields
                        totalBudgetInput.value = budgetState.totalBudget;
                        currencySelect.value = budgetState.currency;
                        
                        // If there are saved categories, load them
                        if (data.budget.categories && data.budget.categories.length > 0) {
                            // Transform the categories to match our expected format
                            const savedCategories = data.budget.categories.map((cat, index) => {
                                return {
                                    id: index + 1,
                                    name: cat.name,
                                    color: getColorForCategory(cat.name, index),
                                    percentage: parseFloat(cat.allocation_percentage)
                                };
                            });
                            
                            // Find if there's a savings category
                            const savingsIndex = savedCategories.findIndex(cat => 
                                cat.name.toLowerCase() === 'savings');
                            
                            if (savingsIndex !== -1) {
                                // If savings exists, mark its ID
                                budgetState.savingsCategory = savedCategories[savingsIndex].id;
                            } else {
                                // If no savings category, add one
                                savedCategories.push({
                                    id: savedCategories.length + 1,
                                    name: 'Savings',
                                    color: '#9b59b6',
                                    percentage: 0 // Will be auto-calculated
                                });
                                budgetState.savingsCategory = savedCategories.length;
                            }
                            
                            budgetState.categories = savedCategories;
                            budgetState.nextId = savedCategories.length + 1;
                            
                            // Update savings category
                            updateSavingsCategory();
                            
                            // Update UI
                            initializeChart();
                            renderCategories();
                            updateBudgetSummary();
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error loading budget:', error);
            });
    }
    
    // Helper function to get a color for a category
    function getColorForCategory(name, index) {
        // Check if it's one of our default categories
        const defaultColors = {
            'accommodation': '#3498db',
            'food': '#e74c3c',
            'transport': '#2ecc71',
            'transportation': '#2ecc71',
            'miscellaneous': '#f1c40f',
            'savings': '#9b59b6'
        };
        
        const lowerName = name.toLowerCase();
        if (defaultColors[lowerName]) {
            return defaultColors[lowerName];
        }
        
        // Use a predefined set of colors for other categories
        const colorPalette = [
            '#1abc9c', '#3498db', '#9b59b6', '#e74c3c', '#f1c40f',
            '#2ecc71', '#e67e22', '#34495e', '#16a085', '#27ae60',
            '#2980b9', '#8e44ad', '#c0392b', '#d35400', '#f39c12'
        ];
        
        return colorPalette[index % colorPalette.length];
    }
    
    // Initialize Chart.js pie chart
    function initializeChart() {
        const ctx = document.getElementById('budgetChart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (budgetChart) {
            budgetChart.destroy();
        }
        
        budgetChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: budgetState.categories.map(cat => cat.name),
                datasets: [{
                    data: budgetState.categories.map(cat => cat.percentage),
                    backgroundColor: budgetState.categories.map(cat => cat.color),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function(label, i) {
                                        const meta = chart.getDatasetMeta(0);
                                        const style = meta.controller.getStyle(i);
                                        return {
                                            text: `${label}: ${data.datasets[0].data[i]}%`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: '#fff',
                                            lineWidth: 2,
                                            hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                const amount = (value / 100) * budgetState.totalBudget;
                                return `${label}: ${value}% (${getCurrencySymbol()}${amount.toFixed(2)})`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Update chart with current data
    function updateChart() {
        budgetChart.data.labels = budgetState.categories.map(cat => cat.name);
        budgetChart.data.datasets[0].data = budgetState.categories.map(cat => cat.percentage);
        budgetChart.data.datasets[0].backgroundColor = budgetState.categories.map(cat => cat.color);
        budgetChart.update();
    }
    
    // Get current currency symbol
    function getCurrencySymbol() {
        return currencySymbols[budgetState.currency] || '$';
    }
    
    // Render category list
    function renderCategories() {
        categoriesList.innerHTML = '';
        
        budgetState.categories.forEach(category => {
            const categoryElement = document.createElement('div');
            categoryElement.className = 'category-item';
            categoryElement.dataset.id = category.id;
            
            // Check if this is the savings category
            const isSavings = category.id === budgetState.savingsCategory;
            const savingsClass = isSavings ? 'savings-category' : '';
            
            categoryElement.innerHTML = `
                <div class="category-col category-name ${savingsClass}">
                    <span class="color-dot" style="background-color: ${category.color}"></span>
                    <span>${category.name}</span>
                </div>
                <div class="category-col percentage-control">
                    <button class="btn-adjust decrease" ${isSavings ? 'disabled' : ''}>-</button>
                    <input type="number" class="percentage-input" value="${category.percentage.toFixed(2)}" min="0" max="100" step="0.01" ${isSavings ? 'readonly' : ''}>
                    <button class="btn-adjust increase" ${isSavings ? 'disabled' : ''}>+</button>
                    <span class="unit">%</span>
                </div>
                <div class="category-col amount-display">
                    <span class="amount">${getCurrencySymbol()}${((category.percentage / 100) * budgetState.totalBudget).toFixed(2)}</span>
                    <button class="remove-category" ${isSavings ? 'disabled' : ''}>&times;</button>
                </div>
            `;
            
            categoriesList.appendChild(categoryElement);
            
            // Add event listeners for the new category (except for Savings)
            if (!isSavings) {
                const percentageInput = categoryElement.querySelector('.percentage-input');
                const decreaseBtn = categoryElement.querySelector('.decrease');
                const increaseBtn = categoryElement.querySelector('.increase');
                const removeBtn = categoryElement.querySelector('.remove-category');
                
                percentageInput.addEventListener('change', () => {
                    // Get current total allocation excluding this category
                    const currentCategoryPercentage = category.percentage;
                    const otherCategoriesTotal = budgetState.categories.reduce((sum, cat) => 
                        cat.id === category.id ? sum : sum + cat.percentage, 0);
                    
                    // Calculate new value making sure it doesn't exceed 100% total
                    let newValue = parseFloat(percentageInput.value);
                    if (isNaN(newValue)) newValue = 0;
                    
                    // Don't limit the input value, but update the summary
                    category.percentage = newValue;
                    
                    // Update the savings category
                    updateSavingsCategory();
                    
                    // Update the display
                    updateChart();
                    renderCategories();
                    updateBudgetSummary();
                });
                
                decreaseBtn.addEventListener('click', () => {
                    adjustCategoryPercentage(category.id, -1);
                });
                
                increaseBtn.addEventListener('click', () => {
                    adjustCategoryPercentage(category.id, 1);
                });
                
                removeBtn.addEventListener('click', () => removeCategory(category.id));
            }
        });
    }
    
    // Update savings category to reflect remaining percentage
    function updateSavingsCategory() {
        // Calculate total percentage from other categories
        const savingsCategory = budgetState.categories.find(cat => cat.id === budgetState.savingsCategory);
        if (!savingsCategory) return;
        
        const otherCategoriesTotal = budgetState.categories.reduce((sum, cat) => 
            cat.id === budgetState.savingsCategory ? sum : sum + cat.percentage, 0);
            
        // Ensure other categories don't exceed 100%
        if (otherCategoriesTotal > 100) {
            // Show error if other categories exceed 100%
            showErrorPopup();
            return;
        }
        
        // Set savings to remaining percentage
        savingsCategory.percentage = Math.max(0, 100 - otherCategoriesTotal);
        
        // Remove error popup if it exists
        removeErrorPopup();
    }
    
    // Update budget summary information
    function updateBudgetSummary() {
        const total = budgetState.categories.reduce((sum, cat) => sum + cat.percentage, 0);
        const remaining = 100 - total;
        const allocatedAmount = (total / 100) * budgetState.totalBudget;
        const remainingAmountValue = budgetState.totalBudget - allocatedAmount;
        
        totalAllocated.textContent = `${total.toFixed(2)}%`;
        totalAllocatedAmount.textContent = `${getCurrencySymbol()}${allocatedAmount.toFixed(2)}`;
        remainingPercentage.textContent = `${remaining.toFixed(2)}%`;
        remainingAmount.textContent = `${getCurrencySymbol()}${remainingAmountValue.toFixed(2)}`;
        
        // Set warning color if over-allocated
        if (total > 100) {
            totalAllocated.classList.add('warning');
            remainingPercentage.classList.add('warning');
            remainingAmount.classList.add('warning');
            
            // Show error popup if over 100%
            showErrorPopup();
        } else {
            totalAllocated.classList.remove('warning');
            remainingPercentage.classList.remove('warning');
            remainingAmount.classList.remove('warning');
            
            // Remove error popup if it exists and we're back under 100%
            removeErrorPopup();
        }
    }
    
    // Create and show error popup
    function showErrorPopup() {
        // Prevent multiple error popups
        if (budgetState.errorPopupActive) return;
        
        const errorPopup = document.createElement('div');
        errorPopup.id = 'errorPopup';
        errorPopup.className = 'error-popup';
        errorPopup.innerHTML = `
            <div class="error-content">
                <div class="error-icon">!</div>
                <div class="error-message">
                    <h3>Budget Exceeded</h3>
                    <p>Your total allocation exceeds 100%. Please adjust your categories.</p>
                </div>
                <button class="close-error">&times;</button>
            </div>
        `;
        
        document.querySelector('.budget-card').appendChild(errorPopup);
        budgetState.errorPopupActive = true;
        
        // Add close button functionality
        errorPopup.querySelector('.close-error').addEventListener('click', removeErrorPopup);
        
        // Auto-hide after 5 seconds
        setTimeout(removeErrorPopup, 5000);
    }
    
    // Remove error popup
    function removeErrorPopup() {
        const errorPopup = document.getElementById('errorPopup');
        if (errorPopup) {
            errorPopup.remove();
            budgetState.errorPopupActive = false;
        }
    }
    
    // Set up all event listeners
    function setupEventListeners() {
        totalBudgetInput.addEventListener('change', function() {
            budgetState.totalBudget = parseFloat(this.value) || 0;
            renderCategories();
            updateBudgetSummary();
        });
        
        currencySelect.addEventListener('change', function() {
            budgetState.currency = this.value;
            renderCategories();
            updateBudgetSummary();
        });
        
        addCategoryBtn.addEventListener('click', addNewCategory);
        
        saveBudgetBtn.addEventListener('click', saveBudget);
        
        toggleChartBtn.addEventListener('click', toggleChart);
    }
    
    // Add a new category
    function addNewCategory() {
        // Calculate remaining budget percentage
        const currentTotal = budgetState.categories.reduce((sum, cat) => sum + cat.percentage, 0);
        const remaining = Math.max(0, 100 - currentTotal);
        
        // Get a random color
        const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16);
        
        const newCategory = {
            id: budgetState.nextId++,
            name: 'New Category',
            color: randomColor,
            percentage: 0 // Start at 0% instead of allocating the remaining budget
        };
        
        budgetState.categories.push(newCategory);
        updateSavingsCategory(); // Update savings after adding new category
        renderCategories();
        updateChart();
        updateBudgetSummary();
        
        // Focus on the new category name for immediate editing
        setTimeout(() => {
            const newItem = categoriesList.querySelector(`[data-id="${newCategory.id}"]`);
            if (newItem) {
                const nameSpan = newItem.querySelector('.category-name span:nth-child(2)');
                nameSpan.setAttribute('contenteditable', 'true');
                nameSpan.focus();
                
                // Add blur event to save name changes
                nameSpan.addEventListener('blur', () => {
                    const newName = nameSpan.textContent.trim();
                    if (newName) {
                        updateCategoryName(newCategory.id, newName);
                    }
                });
                
                // Save on Enter key
                nameSpan.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        nameSpan.blur();
                    }
                });
            }
        }, 0);
    }
    
    // Update category name
    function updateCategoryName(id, newName) {
        const category = budgetState.categories.find(cat => cat.id === id);
        if (category) {
            category.name = newName;
            updateChart();
        }
    }
    
    // Update category percentage
    function updateCategoryPercentage(id, newPercentage) {
        const category = budgetState.categories.find(cat => cat.id === id);
        if (category) {
            category.percentage = Math.max(0, newPercentage);
            
            // Update savings category
            updateSavingsCategory();
            
            updateChart();
            renderCategories();
            updateBudgetSummary();
        }
    }
    
    // Adjust category percentage by increment/decrement
    function adjustCategoryPercentage(id, delta) {
        const category = budgetState.categories.find(cat => cat.id === id);
        if (category) {
            // Calculate new percentage
            let newPercentage = category.percentage + delta;
            
            // Ensure we don't go below 0% when decreasing
            if (delta < 0) {
                newPercentage = Math.max(0, newPercentage);
            }
            
            category.percentage = newPercentage;
            
            // Update savings category
            updateSavingsCategory();
            
            updateChart();
            renderCategories();
            updateBudgetSummary();
        }
    }
    
    // Remove a category
    function removeCategory(id) {
        // Don't allow removing savings category
        if (id === budgetState.savingsCategory) {
            return;
        }
        
        // Always allow removing categories, but keep at least savings and one other
        if (budgetState.categories.length <= 2) {
            alert('You must have at least one category besides Savings.');
            return;
        }
        
        budgetState.categories = budgetState.categories.filter(cat => cat.id !== id);
        
        // Update savings category after removal
        updateSavingsCategory();
        
        updateChart();
        renderCategories();
        updateBudgetSummary();
    }
    
    // Toggle chart visibility
    function toggleChart() {
        if (chartContainer.style.display === 'none') {
            chartContainer.style.display = 'block';
            toggleChartBtn.textContent = 'Hide Chart';
        } else {
            chartContainer.style.display = 'none';
            toggleChartBtn.textContent = 'Show Chart';
        }
    }
    
    // Save budget to the server
    function saveBudget() {
        const total = budgetState.categories.reduce((sum, cat) => 
            cat.id !== budgetState.savingsCategory ? sum + cat.percentage : sum, 0);
        
        // Only check if categories exceed 100%
        if (total > 100) {
            alert('Your budget allocation cannot exceed 100%. Please adjust your categories.');
            return;
        }
        
        // Prepare data for the server
        const budgetData = {
            totalBudget: budgetState.totalBudget,
            currency: budgetState.currency,
            categories: budgetState.categories.map(cat => ({
                name: cat.name,
                percentage: cat.percentage
            }))
        };
        
        // Send data to the server
        fetch('saveBudget.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(budgetData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show success message
                const successMessage = document.createElement('div');
                successMessage.className = 'success-message';
                successMessage.textContent = 'Budget saved successfully!';
                document.querySelector('.budget-card').appendChild(successMessage);
                
                // Remove the message after 3 seconds
                setTimeout(() => {
                    successMessage.remove();
                }, 3000);
            } else {
                // Show error message
                alert('Error saving budget: ' + data.message);
            }
        })
        .catch(error => {
            alert('Failed to save budget. Please try again.');
            console.error('Error:', error);
        });
    }
    
    // Initialize the application
    initialize();
});