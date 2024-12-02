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

        /* Green header */
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
            width: 80%;
            margin-right: 10px;
        }

        #search-btn {
            padding: 10px;
            cursor: pointer;
        }

        /* Table styling */
        .crypto-list {
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .crypto-item {
            background-color: #ffffff;
            border-radius: 8px;
            margin: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 200px;
        }

        .crypto-item h3 {
            font-size: 1.2rem;
            color: #2c3e50;
        }

        .crypto-item p {
            font-size: 1rem;
            color: #27ae60;
            margin: 10px 0;
        }

        .crypto-item button {
            padding: 10px;
            background-color: #27ae60;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
            border-radius: 5px;
        }

        .crypto-item button:hover {
            background-color: #2ecc71;
        }

        /* Footer */
        footer {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 10px;
            text-align: center;
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
            <h2>Cryptocurrency Market</h2>

            <div class="search-container">
                <input type="text" id="search-input" placeholder="Search cryptocurrencies...">
                <button id="search-btn">Search</button>
            </div>

            <div class="crypto-list">
                <?php foreach ($cryptoData as $crypto): ?>
                    <div class="crypto-item">
                        <h3><?php echo $crypto['name']; ?> (<?php echo $crypto['name_abreviation']; ?>)</h3>
                        <p>Price: $<?php echo number_format($crypto['price'], 2); ?></p>
                        <button>Add to Portfolio</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>
</body>
</html>




