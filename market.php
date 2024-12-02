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

        header {
            background-color: #2c3e50;
            padding: 20px;
        }

        header nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header nav h1 a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 1.5rem;
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
            color: #ecf0f1;
            text-decoration: none;
            font-size: 1.1rem;
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
            color: #ecf0f1;
            text-decoration: none;
            font-size: 1rem;
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
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }

        #search-btn:hover {
            background-color: #e67e22;
        }

        .crypto-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .crypto-item {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 250px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .crypto-item:hover {
            transform: scale(1.05);
        }

        .crypto-item h3 {
            font-size: 1.2rem;
            color: #2c3e50;
        }

        .crypto-item p {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin: 10px 0;
        }

        .favorite-btn {
            padding: 5px 10px;
            background-color: #f1c40f;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            border-radius: 4px;
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

        <!-- Search Section -->
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Search for a cryptocurrency...">
            <button id="search-btn" onclick="searchCrypto()">Search</button>
        </div>

        <!-- Crypto List -->
        <div class="crypto-list">
            <?php foreach($cryptoData as $crypto): ?>
                <div class="crypto-item">
                    <h3><?php echo $crypto['name']; ?> (<?php echo $crypto['name_abreviation']; ?>)</h3>
                    <p>Price: $<?php echo number_format($crypto['price'], 2); ?></p>
                    <button class="favorite-btn">Add to Favorites</button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<script>
    function searchCrypto() {
        const input = document.getElementById("search-input").value.toLowerCase();
        const cryptoItems = document.querySelectorAll(".crypto-item");
        
        cryptoItems.forEach(item => {
            const name = item.querySelector("h3").textContent.toLowerCase();
            if (name.includes(input)) {
                item.style.display = "block";
            } else {
                item.style.display = "none";
            }
        });
    }
</script>

</body>
</html>


