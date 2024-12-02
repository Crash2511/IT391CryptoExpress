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
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        #search-btn {
            padding: 10px;
            background-color: #f39c12;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 4px;
        }

        #search-btn:hover {
            background-color: #e67e22;
        }

        /* Crypto Item styling */
        .crypto-item {
            padding: 15px;
            margin: 10px 0;
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .crypto-item h3 {
            color: #27ae60; /* Green for crypto names */
            font-size: 1.25rem;
            margin: 0;
        }

        .crypto-item p {
            font-size: 1rem;
            color: #333;
            margin: 5px 0;
            text-align: center;
        }

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
                <!-- Cryptocurrency data will be injected here dynamically -->
            </div>
        </section>
    </main>

</body>
</html>



