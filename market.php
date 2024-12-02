<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "user";
$password = "Battle2511!";
$dbname = "crypto_express";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cryptocurrency data from the crypto_information table
$sql = "SELECT name, name_abreviation AS symbol, price FROM crypto_information";
$result = $conn->query($sql);

$crypto_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $crypto_data[] = $row;
    }
}

$conn->close();
?>

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
            <h1><a href="index.php">Crypto Express</a></h1>
            <ul class="main-nav">
                <li><a href="index.php">Home</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
                <li><a href="market.php">Market</a></li>
                <li><a href="leaderboard.php">Leaderboard</a></li>
                <li><a href="settings.php">Settings</a></li>
            </ul>
            <ul class="nav-right">
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="add-currency.php" class="add-currency-link">Add Currency</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="market-overview">
            <h2>Market Overview</h2>
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Search for a cryptocurrency..." oninput="searchCrypto()">
            </div>
            <div id="crypto-list">
                <!-- PHP will populate this with the crypto data -->
                <?php foreach ($crypto_data as $crypto) { ?>
                    <div class="crypto-item" data-symbol="<?php echo $crypto['symbol']; ?>">
                        <h3><?php echo $crypto['name']; ?> (<?php echo $crypto['symbol']; ?>)</h3>
                        <p>Price: $<?php echo number_format($crypto['price'], 2); ?></p>
                        <div class="action-btns">
                            <button class="favorite-btn" onclick="toggleFavorite('<?php echo $crypto['symbol']; ?>')">Favorite</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>

    <script>
        // Retrieve favorites from localStorage
        const favorites = JSON.parse(localStorage.getItem('favoriteCryptos')) || [];

        // Mark favorite items on page load
        document.addEventListener('DOMContentLoaded', () => {
            favorites.forEach(symbol => {
                const cryptoItem = document.querySelector(`.crypto-item[data-symbol="${symbol}"]`);
                if (cryptoItem) {
                    const favoriteButton = cryptoItem.querySelector('.favorite-btn');
                    favoriteButton.classList.add('active');
                    favoriteButton.textContent = 'Unfavorite';
                }
            });
        });

        // Function to toggle favorite status
        function toggleFavorite(symbol) {
            const index = favorites.indexOf(symbol);
            if (index > -1) {
                favorites.splice(index, 1); // Remove from favorites
            } else {
                favorites.push(symbol); // Add to favorites
            }
            localStorage.setItem('favoriteCryptos', JSON.stringify(favorites));
            updateFavoritesUI(symbol);
        }

        // Function to update the UI for favorites
        function updateFavoritesUI(symbol) {
            const cryptoItem = document.querySelector(`.crypto-item[data-symbol="${symbol}"]`);
            if (cryptoItem) {
                const favoriteButton = cryptoItem.querySelector('.favorite-btn');
                if (favorites.includes(symbol)) {
                    favoriteButton.classList.add('active');
                    favoriteButton.textContent = 'Unfavorite';
                } else {
                    favoriteButton.classList.remove('active');
                    favoriteButton.textContent = 'Favorite';
                }
            }
        }

        // Function to filter search results
        function searchCrypto() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const cryptoItems = document.querySelectorAll('.crypto-item');
            cryptoItems.forEach(item => {
                const symbol = item.getAttribute('data-symbol').toLowerCase();
                const name = item.querySelector('h3').textContent.toLowerCase();
                if (symbol.includes(searchTerm) || name.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>




