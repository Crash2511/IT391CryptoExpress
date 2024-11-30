<?php
session_start();

// Database connection
$servername = "localhost";
$username = "user";
$password = "password";
$dbname = "crypto_simulator";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cryptocurrency data
$sql = "SELECT name, name_abreviation, price FROM crypto_information ORDER BY name";
$result = $conn->query($sql);
$cryptoData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cryptoData[] = $row;
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
        .crypto-item p {
            text-align: center;
            font-size: 1em;
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
                <input type="text" id="search-input" placeholder="Search for a cryptocurrency...">
                <button id="search-btn" onclick="searchCrypto()">Search</button>
            </div>
            <div id="crypto-list">
                <?php foreach ($cryptoData as $crypto): ?>
                    <div class="crypto-item">
                        <h3><?= htmlspecialchars($crypto['name']) ?> (<?= htmlspecialchars($crypto['name_abreviation']) ?>)</h3>
                        <p>Price: $<?= number_format($crypto['price'], 2) ?></p>
                        <button class="favorite-btn" onclick="toggleFavorite('<?= htmlspecialchars($crypto['name_abreviation']) ?>')">
                            Favorite
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="actions">
            <h2>Buy/Sell</h2>
            <form id="trade-form">
                <label for="crypto">Select Cryptocurrency:</label>
                <select id="crypto" name="crypto">
                    <?php foreach ($cryptoData as $crypto): ?>
                        <option value="<?= htmlspecialchars($crypto['name_abreviation']) ?>">
                            <?= htmlspecialchars($crypto['name']) ?> (<?= htmlspecialchars($crypto['name_abreviation']) ?>)
                        </option>
                    <?php endforeach; ?>
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
        // Load favorites from local storage
        const favorites = JSON.parse(localStorage.getItem('favoriteCryptos')) || [];

        // Function to toggle favorite status
        function toggleFavorite(symbol) {
            const index = favorites.indexOf(symbol);
            if (index > -1) {
                favorites.splice(index, 1); // Remove from favorites
            } else {
                favorites.push(symbol); // Add to favorites
            }
            localStorage.setItem('favoriteCryptos', JSON.stringify(favorites));
            location.reload(); // Re-render the market list
        }

        // Function to search for a cryptocurrency
        function searchCrypto() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const cryptoItems = document.querySelectorAll('.crypto-item');

            cryptoItems.forEach(item => {
                const name = item.querySelector('h3').textContent.toLowerCase();
                if (name.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

