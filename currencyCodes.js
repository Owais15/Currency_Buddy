// currencyCodes.js
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const homeCurrencySelect = document.getElementById('homeCurrency');
    const destCurrencySelect = document.getElementById('destCurrency');
    
    // Currency data - ISO 4217 codes with countries
    const currencyData = [
        { code: "USD", name: "United States Dollar", countries: ["United States", "Ecuador", "El Salvador", "Marshall Islands", "Micronesia", "Palau", "Panama", "Timor-Leste"] },
        { code: "EUR", name: "Euro", countries: ["Andorra", "Austria", "Belgium", "Croatia", "Cyprus", "Estonia", "Finland", "France", "Germany", "Greece", "Ireland", "Italy", "Latvia", "Lithuania", "Luxembourg", "Malta", "Netherlands", "Portugal", "Slovakia", "Slovenia", "Spain"] },
        { code: "GBP", name: "British Pound", countries: ["United Kingdom", "Isle of Man", "Jersey", "Guernsey"] },
        { code: "JPY", name: "Japanese Yen", countries: ["Japan"] },
        { code: "AUD", name: "Australian Dollar", countries: ["Australia", "Christmas Island", "Cocos Islands", "Kiribati", "Nauru", "Norfolk Island", "Tuvalu"] },
        { code: "CAD", name: "Canadian Dollar", countries: ["Canada"] },
        { code: "CHF", name: "Swiss Franc", countries: ["Switzerland", "Liechtenstein"] },
        { code: "CNY", name: "Chinese Yuan", countries: ["China"] },
        { code: "HKD", name: "Hong Kong Dollar", countries: ["Hong Kong"] },
        { code: "NZD", name: "New Zealand Dollar", countries: ["New Zealand", "Cook Islands", "Niue", "Pitcairn Islands", "Tokelau"] },
        { code: "SEK", name: "Swedish Krona", countries: ["Sweden"] },
        { code: "KRW", name: "South Korean Won", countries: ["South Korea"] },
        { code: "SGD", name: "Singapore Dollar", countries: ["Singapore", "Brunei"] },
        { code: "NOK", name: "Norwegian Krone", countries: ["Norway", "Svalbard", "Jan Mayen"] },
        { code: "MXN", name: "Mexican Peso", countries: ["Mexico"] },
        { code: "INR", name: "Indian Rupee", countries: ["India", "Bhutan"] },
        { code: "RUB", name: "Russian Ruble", countries: ["Russia"] },
        { code: "ZAR", name: "South African Rand", countries: ["South Africa", "Lesotho", "Namibia"] },
        { code: "TRY", name: "Turkish Lira", countries: ["Turkey"] },
        { code: "BRL", name: "Brazilian Real", countries: ["Brazil"] },
        { code: "TWD", name: "New Taiwan Dollar", countries: ["Taiwan"] },
        { code: "DKK", name: "Danish Krone", countries: ["Denmark", "Faroe Islands", "Greenland"] },
        { code: "PLN", name: "Polish Złoty", countries: ["Poland"] },
        { code: "THB", name: "Thai Baht", countries: ["Thailand"] },
        { code: "IDR", name: "Indonesian Rupiah", countries: ["Indonesia"] },
        { code: "CZK", name: "Czech Koruna", countries: ["Czech Republic"] },
        { code: "AED", name: "United Arab Emirates Dirham", countries: ["United Arab Emirates"] },
        { code: "ARS", name: "Argentine Peso", countries: ["Argentina"] },
        { code: "CLP", name: "Chilean Peso", countries: ["Chile"] },
        { code: "EGP", name: "Egyptian Pound", countries: ["Egypt"] },
        { code: "ILS", name: "Israeli New Shekel", countries: ["Israel", "Palestinian territories"] },
        { code: "MYR", name: "Malaysian Ringgit", countries: ["Malaysia"] },
        { code: "PHP", name: "Philippine Peso", countries: ["Philippines"] },
        { code: "RON", name: "Romanian Leu", countries: ["Romania"] },
        { code: "SAR", name: "Saudi Riyal", countries: ["Saudi Arabia"] },
        { code: "AED", name: "UAE Dirham", countries: ["United Arab Emirates"] },
        { code: "AFN", name: "Afghan Afghani", countries: ["Afghanistan"] },
        { code: "ALL", name: "Albanian Lek", countries: ["Albania"] },
        { code: "AMD", name: "Armenian Dram", countries: ["Armenia"] },
        { code: "ANG", name: "Netherlands Antillean Guilder", countries: ["Curaçao", "Sint Maarten"] },
        { code: "AOA", name: "Angolan Kwanza", countries: ["Angola"] },
        { code: "AWG", name: "Aruban Florin", countries: ["Aruba"] },
        { code: "AZN", name: "Azerbaijani Manat", countries: ["Azerbaijan"] },
        { code: "BAM", name: "Bosnia and Herzegovina Convertible Mark", countries: ["Bosnia and Herzegovina"] },
        { code: "BBD", name: "Barbadian Dollar", countries: ["Barbados"] },
        { code: "BDT", name: "Bangladeshi Taka", countries: ["Bangladesh"] },
        { code: "BGN", name: "Bulgarian Lev", countries: ["Bulgaria"] },
        { code: "BHD", name: "Bahraini Dinar", countries: ["Bahrain"] },
        { code: "BIF", name: "Burundian Franc", countries: ["Burundi"] },
        { code: "BMD", name: "Bermudian Dollar", countries: ["Bermuda"] },
        { code: "BND", name: "Brunei Dollar", countries: ["Brunei"] },
        { code: "BOB", name: "Bolivian Boliviano", countries: ["Bolivia"] },
        { code: "BSD", name: "Bahamian Dollar", countries: ["Bahamas"] },
        { code: "BTN", name: "Bhutanese Ngultrum", countries: ["Bhutan"] },
        { code: "BWP", name: "Botswana Pula", countries: ["Botswana"] },
        { code: "BYN", name: "Belarusian Ruble", countries: ["Belarus"] },
        { code: "BZD", name: "Belize Dollar", countries: ["Belize"] },
        { code: "CDF", name: "Congolese Franc", countries: ["Democratic Republic of the Congo"] },
        { code: "COP", name: "Colombian Peso", countries: ["Colombia"] },
        { code: "CRC", name: "Costa Rican Colón", countries: ["Costa Rica"] },
        { code: "CUP", name: "Cuban Peso", countries: ["Cuba"] },
        { code: "CVE", name: "Cape Verdean Escudo", countries: ["Cape Verde"] },
        { code: "DJF", name: "Djiboutian Franc", countries: ["Djibouti"] },
        { code: "DOP", name: "Dominican Peso", countries: ["Dominican Republic"] },
        { code: "DZD", name: "Algerian Dinar", countries: ["Algeria"] },
        { code: "ERN", name: "Eritrean Nakfa", countries: ["Eritrea"] },
        { code: "ETB", name: "Ethiopian Birr", countries: ["Ethiopia"] },
        { code: "FJD", name: "Fijian Dollar", countries: ["Fiji"] },
        { code: "GEL", name: "Georgian Lari", countries: ["Georgia"] },
        { code: "GHS", name: "Ghanaian Cedi", countries: ["Ghana"] },
        { code: "GMD", name: "Gambian Dalasi", countries: ["Gambia"] },
        { code: "GNF", name: "Guinean Franc", countries: ["Guinea"] },
        { code: "GTQ", name: "Guatemalan Quetzal", countries: ["Guatemala"] },
        { code: "GYD", name: "Guyanese Dollar", countries: ["Guyana"] },
        { code: "HNL", name: "Honduran Lempira", countries: ["Honduras"] },
        { code: "HRK", name: "Croatian Kuna", countries: ["Croatia"] },
        { code: "HTG", name: "Haitian Gourde", countries: ["Haiti"] },
        { code: "HUF", name: "Hungarian Forint", countries: ["Hungary"] },
        { code: "IQD", name: "Iraqi Dinar", countries: ["Iraq"] },
        { code: "IRR", name: "Iranian Rial", countries: ["Iran"] },
        { code: "ISK", name: "Icelandic Króna", countries: ["Iceland"] },
        { code: "JMD", name: "Jamaican Dollar", countries: ["Jamaica"] },
        { code: "JOD", name: "Jordanian Dinar", countries: ["Jordan"] },
        { code: "KES", name: "Kenyan Shilling", countries: ["Kenya"] },
        { code: "KGS", name: "Kyrgyzstani Som", countries: ["Kyrgyzstan"] },
        { code: "KHR", name: "Cambodian Riel", countries: ["Cambodia"] },
        { code: "KMF", name: "Comorian Franc", countries: ["Comoros"] },
        { code: "KWD", name: "Kuwaiti Dinar", countries: ["Kuwait"] },
        { code: "KZT", name: "Kazakhstani Tenge", countries: ["Kazakhstan"] },
        { code: "LAK", name: "Lao Kip", countries: ["Laos"] },
        { code: "LBP", name: "Lebanese Pound", countries: ["Lebanon"] },
        { code: "LKR", name: "Sri Lankan Rupee", countries: ["Sri Lanka"] },
        { code: "LRD", name: "Liberian Dollar", countries: ["Liberia"] },
        { code: "LSL", name: "Lesotho Loti", countries: ["Lesotho"] },
        { code: "LYD", name: "Libyan Dinar", countries: ["Libya"] },
        { code: "MAD", name: "Moroccan Dirham", countries: ["Morocco", "Western Sahara"] },
        { code: "MDL", name: "Moldovan Leu", countries: ["Moldova"] },
        { code: "MGA", name: "Malagasy Ariary", countries: ["Madagascar"] },
        { code: "MKD", name: "Macedonian Denar", countries: ["North Macedonia"] },
        { code: "MMK", name: "Myanmar Kyat", countries: ["Myanmar"] },
        { code: "MNT", name: "Mongolian Tögrög", countries: ["Mongolia"] },
        { code: "MOP", name: "Macanese Pataca", countries: ["Macau"] },
        { code: "MRU", name: "Mauritanian Ouguiya", countries: ["Mauritania"] },
        { code: "MUR", name: "Mauritian Rupee", countries: ["Mauritius"] },
        { code: "MVR", name: "Maldivian Rufiyaa", countries: ["Maldives"] },
        { code: "MWK", name: "Malawian Kwacha", countries: ["Malawi"] },
        { code: "MZN", name: "Mozambican Metical", countries: ["Mozambique"] },
        { code: "NAD", name: "Namibian Dollar", countries: ["Namibia"] },
        { code: "NGN", name: "Nigerian Naira", countries: ["Nigeria"] },
        { code: "NIO", name: "Nicaraguan Córdoba", countries: ["Nicaragua"] },
        { code: "NPR", name: "Nepalese Rupee", countries: ["Nepal"] },
        { code: "OMR", name: "Omani Rial", countries: ["Oman"] },
        { code: "PAB", name: "Panamanian Balboa", countries: ["Panama"] },
        { code: "PEN", name: "Peruvian Sol", countries: ["Peru"] },
        { code: "PGK", name: "Papua New Guinean Kina", countries: ["Papua New Guinea"] },
        { code: "PKR", name: "Pakistani Rupee", countries: ["Pakistan"] },
        { code: "PYG", name: "Paraguayan Guaraní", countries: ["Paraguay"] },
        { code: "QAR", name: "Qatari Riyal", countries: ["Qatar"] },
        { code: "RSD", name: "Serbian Dinar", countries: ["Serbia"] },
        { code: "RWF", name: "Rwandan Franc", countries: ["Rwanda"] },
        { code: "SBD", name: "Solomon Islands Dollar", countries: ["Solomon Islands"] },
        { code: "SCR", name: "Seychellois Rupee", countries: ["Seychelles"] },
        { code: "SDG", name: "Sudanese Pound", countries: ["Sudan"] },
        { code: "SLL", name: "Sierra Leonean Leone", countries: ["Sierra Leone"] },
        { code: "SOS", name: "Somali Shilling", countries: ["Somalia"] },
        { code: "SRD", name: "Surinamese Dollar", countries: ["Suriname"] },
        { code: "SSP", name: "South Sudanese Pound", countries: ["South Sudan"] },
        { code: "STN", name: "São Tomé and Príncipe Dobra", countries: ["São Tomé and Príncipe"] },
        { code: "SVC", name: "Salvadoran Colón", countries: ["El Salvador"] },
        { code: "SYP", name: "Syrian Pound", countries: ["Syria"] },
        { code: "SZL", name: "Swazi Lilangeni", countries: ["Eswatini"] },
        { code: "TJS", name: "Tajikistani Somoni", countries: ["Tajikistan"] },
        { code: "TMT", name: "Turkmenistan Manat", countries: ["Turkmenistan"] },
        { code: "TND", name: "Tunisian Dinar", countries: ["Tunisia"] },
        { code: "TOP", name: "Tongan Paʻanga", countries: ["Tonga"] },
        { code: "TTD", name: "Trinidad and Tobago Dollar", countries: ["Trinidad and Tobago"] },
        { code: "TZS", name: "Tanzanian Shilling", countries: ["Tanzania"] },
        { code: "UAH", name: "Ukrainian Hryvnia", countries: ["Ukraine"] },
        { code: "UGX", name: "Ugandan Shilling", countries: ["Uganda"] },
        { code: "UYU", name: "Uruguayan Peso", countries: ["Uruguay"] },
        { code: "UZS", name: "Uzbekistani Som", countries: ["Uzbekistan"] },
        { code: "VES", name: "Venezuelan Bolívar Soberano", countries: ["Venezuela"] },
        { code: "VND", name: "Vietnamese Đồng", countries: ["Vietnam"] },
        { code: "VUV", name: "Vanuatu Vatu", countries: ["Vanuatu"] },
        { code: "WST", name: "Samoan Tālā", countries: ["Samoa"] },
        { code: "XAF", name: "Central African CFA Franc", countries: ["Cameroon", "Central African Republic", "Chad", "Republic of the Congo", "Equatorial Guinea", "Gabon"] },
        { code: "XCD", name: "East Caribbean Dollar", countries: ["Antigua and Barbuda", "Dominica", "Grenada", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Anguilla", "Montserrat"] },
        { code: "XOF", name: "West African CFA Franc", countries: ["Benin", "Burkina Faso", "Guinea-Bissau", "Ivory Coast", "Mali", "Niger", "Senegal", "Togo"] },
        { code: "XPF", name: "CFP Franc", countries: ["French Polynesia", "New Caledonia", "Wallis and Futuna"] },
        { code: "YER", name: "Yemeni Rial", countries: ["Yemen"] },
        { code: "ZMW", name: "Zambian Kwacha", countries: ["Zambia"] },
        { code: "ZWL", name: "Zimbabwean Dollar", countries: ["Zimbabwe"] },
    ];
    
    // Populate select elements with currencies
    populateCurrencySelects();
    
    // Listen for search in currency selects
    setupSearchListeners();
    
    // Function to populate currency selects
    function populateCurrencySelects() {
        // Sort by currency code
        const sortedCurrencies = [...currencyData].sort((a, b) => {
            return a.code.localeCompare(b.code);
        });
        
        // Create optgroups for each first letter
        const groups = {};
        sortedCurrencies.forEach(currency => {
            const firstLetter = currency.code.charAt(0);
            if (!groups[firstLetter]) {
                groups[firstLetter] = document.createElement('optgroup');
                groups[firstLetter].label = firstLetter;
            }
            
            // Create option with currency code, name and countries
            const option = document.createElement('option');
            option.value = currency.code;
            option.textContent = `${currency.code} - ${currency.name}`;
            option.dataset.countries = currency.countries.join(', ');
            
            // Add option to optgroup
            groups[firstLetter].appendChild(option);
        });
        
        // Add optgroups to selects
        Object.values(groups).forEach(group => {
            const homeClone = group.cloneNode(true);
            const destClone = group.cloneNode(true);
            homeCurrencySelect.appendChild(homeClone);
            destCurrencySelect.appendChild(destClone);
        });
        
        // Set default values (USD for home and EUR for destination)
        homeCurrencySelect.value = 'USD';
        destCurrencySelect.value = 'EUR';
        
        // Trigger change event to fetch initial exchange rate
        const event = new Event('change');
        homeCurrencySelect.dispatchEvent(event);
    }
    
    // Setup search functionality for selects
    function setupSearchListeners() {
        // Add search functionality to currency selects
        [homeCurrencySelect, destCurrencySelect].forEach(select => {
            select.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') return;
                
                const query = this.value.toLowerCase();
                
                // If no input or only backspace/delete, reset
                if (!query || e.key === 'Backspace' || e.key === 'Delete') {
                    resetSelectOptions(this);
                    return;
                }
                
                // Filter options based on search
                searchCurrencies(this, query);
            });
        });
    }
    
    // Reset select options to show all
    function resetSelectOptions(select) {
        const optgroups = select.querySelectorAll('optgroup');
        optgroups.forEach(group => {
            const options = group.querySelectorAll('option');
            options.forEach(option => {
                option.style.display = '';
            });
            group.style.display = '';
        });
    }
    
    // Search currencies by code, name or country
    function searchCurrencies(select, query) {
        const optgroups = select.querySelectorAll('optgroup');
        
        optgroups.forEach(group => {
            let hasVisibleOption = false;
            const options = group.querySelectorAll('option');
            
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                const countries = option.dataset.countries.toLowerCase();
                
                if (text.includes(query) || countries.includes(query)) {
                    option.style.display = '';
                    hasVisibleOption = true;
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Show/hide optgroup based on whether it has visible options
            group.style.display = hasVisibleOption ? '' : 'none';
        });
    }
    
    // Export the currency data for potential use in other scripts
    window.currencyData = currencyData;
});