<?php
session_start();

// Database connection
$servername = "localhost";
$username = "user";
$password = "Battle2511!";
$dbname = "crypto_express";

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
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        /* Reverting the home bar color and size */
        header {
            background-color: #27ae60; /* Green color for the header */
            padding: 20px;
        }

        header nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header nav h1 a {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.75rem; /* Standardize the font size for consistency */
        }

        .main-nav {
            list-style: none;
            display: flex;
            margin: 0;
        }

        .main-nav li {
            margin-left: 20px;
        }

        .main-nav a {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.2rem; /* Increase font size for better readability */
        }

        .nav-right {
            list-style: none;
            display: flex;
            margin: 0;
        }

        .nav-right li {
            margin-left: 20px;
        }

        .nav-right a {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.1rem;
        }

        /* Market Overview */
        main {
            padding: 40px 20px;
            max-width: 1200px;
            margin: auto;
        }

        #market-overview h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        #search-input {
            padding: 10px;
            width: 70%;
            margin-right: 10px;
        }

        #search-btn {
            padding: 10px;
            background-color: #f39c12;
            color: white;
            border: none;
            cursor: pointer;
        }

        #crypto-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .crypto-item {
            background-color: white;
            padding: 20px;
            margin: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 200px;
            text-align: center;
        }

        .crypto-item h3 {
            margin: 10px 0;
            font-size: 1.2rem;
        }

        .crypto-item p {
            font-size: 1rem;
            color: #27ae60;
            margin-bottom: 10px;
        }

        .crypto-item .favorite-btn {
            padding: 5px 10px;
            background-color: #f1c40f;
            border: none;
            cursor: pointer;
        }

        /* Footer styles */
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        footer p {
            margin: 0;
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
                <!-- Sample cryptocurrency data displayed -->
                <?php foreach ($cryptoData as $crypto): ?>
                    <div class="crypto-item">
                        <h3><?= $crypto['name'] ?> (<?= $crypto['name_abreviation'] ?>)</h3>
                        <p>$<?= number_format($crypto['price'], 2) ?></p>
                        <button class="favorite-btn">Add to Favorites</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express. All Rights Reserved.</p>
    </footer>

    <script>
        // Dummy search function for demonstration purposes
        function searchCrypto() {
            let searchQuery = document.getElementById('search-input').value.toLowerCase();
            let cryptoItems = document.querySelectorAll('.crypto-item');
            cryptoItems.forEach(item => {
                let name = item.querySelector('h3').textContent.toLowerCase();
                if (name.includes(searchQuery)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>




