// budgetCalculator.js
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const homeCurrencySelect = document.getElementById('homeCurrency');
    const destCurrencySelect = document.getElementById('destCurrency');
    const tripDurationInput = document.getElementById('tripDuration');
    const calculateBtn = document.getElementById('calculateBtn');
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const budgetSummary = document.getElementById('budgetSummary');
    const homeCurrencyCode = document.getElementById('homeCurrencyCode');
    const destCurrencyCode = document.getElementById('destCurrencyCode');
    const homeCurrencyTotal = document.getElementById('homeCurrencyTotal');
    const destCurrencyTotal = document.getElementById('destCurrencyTotal');
    const homeHeader = document.getElementById('homeHeader');
    const destHeader = document.getElementById('destHeader');
    const expenseTable = document.getElementById('expenseTable').querySelector('tbody');
    const radioButtons = document.querySelectorAll('input[name="entryType"]');
    
    // State variables
    let exchangeRate = 1;
    let customCategoryCounter = 1;
    let selectedCurrency = 'home'; // Default input currency is home
    
    // Initialize the calculator
    init();
    
    function init() {
        // Event listeners
        homeCurrencySelect.addEventListener('change', fetchExchangeRate);
        destCurrencySelect.addEventListener('change', fetchExchangeRate);
        calculateBtn.addEventListener('click', calculateBudget);
        addCategoryBtn.addEventListener('click', showAddCategoryModal);
        
        // Radio button listeners
        radioButtons.forEach(button => {
            button.addEventListener('change', function() {
                selectedCurrency = this.value;
            });
        });
        
        // Setup calendar button
        const calendarBtn = document.querySelector('.calendar-btn');
        calendarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Could implement a date picker here in the future
            alert('Calendar functionality coming soon!');
        });
        
        // Create modal for adding new categories
        createAddCategoryModal();
        
        // Add delete buttons to existing categories
        addDeleteButtonsToExistingCategories();
    }
    
    // Add delete buttons to pre-existing categories
    function addDeleteButtonsToExistingCategories() {
        const existingCategories = document.querySelectorAll('.expense-category:not(.custom-category)');
        
        existingCategories.forEach(category => {
            // Create delete button
            const deleteButton = document.createElement('button');
            deleteButton.className = 'remove-category';
            deleteButton.title = 'Remove Category';
            deleteButton.innerHTML = '<i class="fas fa-times"></i>';
            
            // Add event listener
            deleteButton.addEventListener('click', function() {
                const categoriesContainer = category.parentElement;
                categoriesContainer.removeChild(category);
            });
            
            // Add button to category
            category.appendChild(deleteButton);
            // Add custom-category class to make styling consistent
            category.classList.add('custom-category');
        });
    }
    
    // Fetch exchange rate from API
    function fetchExchangeRate() {
        const homeCurrency = homeCurrencySelect.value;
        const destCurrency = destCurrencySelect.value;
        
        if (!homeCurrency || !destCurrency) return;
        
        // Update UI with selected currencies
        homeCurrencyCode.textContent = homeCurrency;
        destCurrencyCode.textContent = destCurrency;
        homeHeader.textContent = homeCurrency;
        destHeader.textContent = destCurrency;
        
        // Show loading state
        calculateBtn.innerHTML = '<span class="loading-spinner"></span> Loading...';
        calculateBtn.disabled = true;
        
        // Use Exchange Rate API to get real-time rates
        const apiUrl = `https://api.exchangerate-api.com/v4/latest/${homeCurrency}`;
        
        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                exchangeRate = data.rates[destCurrency];
                console.log(`Exchange rate from ${homeCurrency} to ${destCurrency}: ${exchangeRate}`);
                
                // Enable calculate button
                calculateBtn.innerHTML = '<i class="fas fa-calculator"></i> Calculate Budget';
                calculateBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching exchange rate:', error);
                alert('Failed to fetch exchange rate. Please try again.');
                
                // Reset button
                calculateBtn.innerHTML = '<i class="fas fa-calculator"></i> Calculate Budget';
                calculateBtn.disabled = false;
            });
    }
    
    // Calculate the budget
    function calculateBudget() {
        const days = parseInt(tripDurationInput.value) || 1;
        const expenseInputs = document.querySelectorAll('.expense-input');
        
        let totalHomeAmount = 0;
        let totalDestAmount = 0;
        let categories = [];
        
        // Clear the table
        expenseTable.innerHTML = '';
        
        expenseInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            if (value === 0) return; // Skip empty inputs
            
            const category = input.dataset.category;
            const categoryElement = document.querySelector(`#${category}`);
            
            // Skip if category has been deleted
            if (!categoryElement) return;
            
            const categoryName = categoryElement.querySelector('.category-name').textContent;
            
            let homeAmount, destAmount;
            
            // Calculate based on selected currency
            if (selectedCurrency === 'home') {
                homeAmount = value * days;
                destAmount = homeAmount * exchangeRate;
            } else {
                destAmount = value * days;
                homeAmount = destAmount / exchangeRate;
            }
            
            totalHomeAmount += homeAmount;
            totalDestAmount += destAmount;
            
            // Add to categories for table display
            categories.push({
                name: categoryName,
                homeAmount: homeAmount,
                destAmount: destAmount
            });
        });
        
        // Update the summary
        homeCurrencyTotal.textContent = totalHomeAmount.toFixed(2);
        destCurrencyTotal.textContent = totalDestAmount.toFixed(2);
        
        // Build the table
        categories.forEach(category => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${category.name}</td>
                <td>${category.homeAmount.toFixed(2)}</td>
                <td>${category.destAmount.toFixed(2)}</td>
            `;
            expenseTable.appendChild(row);
        });
        
        // Show the summary
        budgetSummary.classList.add('active');
    }
    
    // Create modal for adding new category
    function createAddCategoryModal() {
        const modal = document.createElement('div');
        modal.id = 'addCategoryModal';
        modal.className = 'modal';
        
        const icons = [
            'fa-coffee', 'fa-gift', 'fa-medkit', 'fa-wifi',
            'fa-taxi', 'fa-camera', 'fa-glass-martini', 'fa-umbrella-beach',
            'fa-plane', 'fa-train', 'fa-ship', 'fa-ticket-alt'
        ];
        
        let iconOptionsHTML = '';
        icons.forEach(icon => {
            iconOptionsHTML += `
                <div class="icon-option" data-icon="${icon}">
                    <i class="fas ${icon}"></i>
                </div>
            `;
        });
        
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h3>Add New Expense Category</h3>
                <div class="form-group">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" class="form-control" placeholder="e.g., Souvenirs">
                </div>
                <div class="form-group">
                    <label>Select Icon</label>
                    <div class="icon-selection">
                        ${iconOptionsHTML}
                    </div>
                </div>
                <div class="modal-buttons">
                    <button class="btn btn-secondary" id="cancelAddCategory">Cancel</button>
                    <button class="btn btn-primary" id="confirmAddCategory">Add Category</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Event listeners for modal
        const closeModalBtn = modal.querySelector('.close-modal');
        const cancelBtn = modal.querySelector('#cancelAddCategory');
        const confirmBtn = modal.querySelector('#confirmAddCategory');
        const iconOptions = modal.querySelectorAll('.icon-option');
        
        let selectedIcon = null;
        
        iconOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                iconOptions.forEach(opt => opt.classList.remove('selected'));
                // Add selected class to clicked option
                this.classList.add('selected');
                selectedIcon = this.dataset.icon;
            });
        });
        
        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        confirmBtn.addEventListener('click', addNewCategory);
        
        function closeModal() {
            modal.style.display = 'none';
            document.getElementById('categoryName').value = '';
            iconOptions.forEach(opt => opt.classList.remove('selected'));
            selectedIcon = null;
        }
        
        function addNewCategory() {
            const categoryName = document.getElementById('categoryName').value.trim();
            
            if (!categoryName) {
                alert('Please enter a category name');
                return;
            }
            
            if (!selectedIcon) {
                alert('Please select an icon');
                return;
            }
            
            // Create new category element
            const categoryId = `custom-category-${customCategoryCounter++}`;
            const categoriesContainer = document.querySelector('.expense-categories');
            
            const newCategory = document.createElement('div');
            newCategory.className = 'expense-category custom-category';
            newCategory.id = categoryId;
            
            newCategory.innerHTML = `
                <button class="remove-category" title="Remove Category">
                    <i class="fas fa-times"></i>
                </button>
                <div class="category-icon">
                    <i class="fas ${selectedIcon}"></i>
                </div>
                <div class="category-name">${categoryName}</div>
                <div class="category-input">
                    <input type="number" class="form-control expense-input" data-category="${categoryId}" min="0">
                    <span class="per-day">per day</span>
                </div>
            `;
            
            categoriesContainer.appendChild(newCategory);
            
            // Add event listener to remove button
            const removeBtn = newCategory.querySelector('.remove-category');
            removeBtn.addEventListener('click', function() {
                categoriesContainer.removeChild(newCategory);
            });
            
            closeModal();
        }
    }
    
    // Show the add category modal
    function showAddCategoryModal() {
        const modal = document.getElementById('addCategoryModal');
        modal.style.display = 'block';
    }
});