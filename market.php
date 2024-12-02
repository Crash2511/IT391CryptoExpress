<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .favorite-btn {
            padding: 5px 10px;
            background-color: #f1c40f;
            border: none;
            cursor: pointer;
            margin-top: 5px;
        }

        .favorite-btn.active {
            background-color: #e67e22;
        }

        .search-container {
            margin: 20px 0;
            text-align: center;
        }

        #search-input {
            padding: 10px;
            width: 80%;
            margin-right: 10px;
        }

        #search-btn {
            padding: 10px;
            cursor: pointer;
        }

        /* Center-align and format prices */
        .crypto-item {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }

        .crypto-item p {
            font-size: 1em;
        }

        .crypto-details {
            margin-top: 10px;
        }

        .action-btns {
            margin-top: 10px;
        }

        .action-btns button {
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1><a href="index.html">Crypto Express</a></h1>
            <ul class="main-nav">
                <li><a href="index.html">Home</a></li>
                <li><a href="portfolio.html">Portfolio</a></li>
                <li><a href="market.html">Market</a></li>
                <li><a href="leaderboard.html">Leaderboard</a></li>
                <li><a href="settings.html">Settings</a></li>
            </ul>
            <ul class="nav-right">
                <li><a href="login.html">Login</a></li>
                <li><a href="register.html">Register</a></li>
                <li><a href="add-currency.html" class="add-currency-link">Add Currency</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="market-overview">
            <h2>Market Overview</h2>
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Search for a cryptocurrency..." oninput="searchCrypto()">
                <button id="search-btn" onclick="searchCrypto()">Search</button>
            </div>
            <div id="crypto-list">
                <!-- JavaScript will populate this -->
            </div>
        </section>

        <section id="actions">
            <h2>Buy/Sell</h2>
            <form id="trade-form">
                <label for="crypto">Select Cryptocurrency:</label>
                <select id="crypto" name="crypto">
                    <!-- JavaScript will populate this -->
                </select>

                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" min="1">

                <button type="button" id="buy-btn">Buy</button>
                <button type="button" id="sell-btn">Sell</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>

    <script>
        // Mock data for top cryptocurrencies
        const cryptoData = [
            { name: 'Bitcoin', symbol: 'BTC', price: 34000 },
            { name: 'Ethereum', symbol: 'ETH', price: 1900 },
            { name: 'Binance Coin', symbol: 'BNB', price: 540 },
            { name: 'Ripple', symbol: 'XRP', price: 0.5 },
            { name: 'Cardano', symbol: 'ADA', price: 0.25 }
        ];

        // Function to search for a cryptocurrency
        function searchCrypto() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const filteredData = cryptoData.filter(crypto =>
                crypto.name.toLowerCase().includes(searchTerm) || crypto.symbol.toLowerCase().includes(searchTerm)
            );
            displaySearchResults(filteredData);
        }

        // Function to display search results
        function displaySearchResults(filteredData) {
            const cryptoListDiv = document.getElementById('crypto-list');
            cryptoListDiv.innerHTML = ''; // Clear previous results

            if (filteredData.length === 0) {
                cryptoListDiv.innerHTML = '<p>No cryptocurrencies found.</p>';
                return;
            }

            filteredData.forEach(crypto => {
                const cryptoItem = document.createElement('div');
                cryptoItem.classList.add('crypto-item');
                cryptoItem.innerHTML = `
                    <h3>${crypto.name} (${crypto.symbol})</h3>
                    <p>Price: $${crypto.price.toFixed(2)}</p>
                    <div class="crypto-details">
                        <p>Details: ${crypto.name} is a popular cryptocurrency.</p>
                    </div>
                    <div class="action-btns">
                        <button class="favorite-btn" onclick="toggleFavorite('${crypto.symbol}')">
                            ${isFavorite(crypto.symbol) ? 'Unfavorite' : 'Favorite'}
                        </button>
                        <button class="buy-btn" onclick="buyCrypto('${crypto.symbol}')">Buy</button>
                        <button class="sell-btn" onclick="sellCrypto('${crypto.symbol}')">Sell</button>
                    </div>
                `;
                cryptoListDiv.appendChild(cryptoItem);
            });
        }

        // Function to check if a crypto is a favorite
        function isFavorite(symbol) {
            return favorites.includes(symbol);
        }

        // Function to toggle favorite status
        function toggleFavorite(symbol) {
            const index = favorites.indexOf(symbol);
            if (index > -1) {
                favorites.splice(index, 1); // Remove from favorites
            } else {
                favorites.push(symbol); // Add to favorites
            }
            localStorage.setItem('favoriteCryptos', JSON.stringify(favorites));
        }

        // Handle buy action
        function buyCrypto(symbol) {
            const amount = document.getElementById('amount').value;
            if (amount <= 0) {
                alert('Please enter a valid amount.');
                return;
            }
            alert(`You bought ${amount} of ${symbol}!`);
        }

        // Handle sell action
        function sellCrypto(symbol) {
            const amount = document.getElementById('amount').value;
            if (amount <= 0) {
                alert('Please enter a valid amount.');
                return;
            }
            alert(`You sold ${amount} of ${symbol}!`);
        }

        // Run populateMarket on page load
        window.onload = function() {
            displaySearchResults(cryptoData);
        };
    </script>
</body>
</html>





