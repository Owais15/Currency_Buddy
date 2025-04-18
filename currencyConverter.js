// currency-converter.js
document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const fromCurrency = document.getElementById('from_currency');
    const toCurrency = document.getElementById('to_currency');
    const fromFlag = document.getElementById('fromFlag');
    const toFlag = document.getElementById('toFlag');
    const swapButton = document.getElementById('swap-currencies');
    const favoritesCurrencies = document.querySelectorAll('.favorite-currency');
    const form = document.getElementById('converter-form');
    
    // Initialize flags
    updateFlag(fromCurrency, fromFlag);
    updateFlag(toCurrency, toFlag);
    
    // Event listeners
    fromCurrency.addEventListener('change', function() {
        updateFlag(fromCurrency, fromFlag);
    });
    
    toCurrency.addEventListener('change', function() {
        updateFlag(toCurrency, toFlag);
    });
    
    swapButton.addEventListener('click', function() {
        const tempCurrency = fromCurrency.value;
        fromCurrency.value = toCurrency.value;
        toCurrency.value = tempCurrency;
        
        updateFlag(fromCurrency, fromFlag);
        updateFlag(toCurrency, toFlag);
    });
    
    // Add event listeners to favorite currencies
    favoritesCurrencies.forEach(function(favorite) {
        favorite.addEventListener('click', function() {
            const currencyCode = this.getAttribute('data-code');
            toCurrency.value = currencyCode;
            updateFlag(toCurrency, toFlag);
            form.submit();
        });
    });
    
    // Function to update flag image
    function updateFlag(selectElement, flagElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const countryCode = selectedOption.getAttribute('data-country');
        flagElement.src = `https://flagsapi.com/${countryCode}/flat/64.png`;
    }
});