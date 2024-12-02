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

        /* Header matching the index page */
        header {
            background-color: #2c3e50; /* Dark green like the one from index */
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
            color: white;
            border: none;
            cursor: pointer;
        }

        /* Table for cryptocurrency data */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        /* Footer with copyright */
        footer {
            background-color: #000;
            color: white;
            padding: 10px 0;
            text-align: center;
            font-size: 1rem;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>

<header>
    <nav>
        <h1><a href="index.php">Crypto Express</a></h1>
        <ul class="main-nav">
            <li><a href="market.php">Market</a></li>
            <li><a href="portfolio.php">Portfolio</a></li>
            <li><a href="settings.php">Settings</a></li>
        </ul>
        <ul class="nav-right">
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <div id="market-overview">
        <h2>Cryptocurrency Market</h2>
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Search for a cryptocurrency...">
            <button id="search-btn">Search</button>
        </div>
        
        <!-- Table for crypto data -->
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Abbreviation</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cryptoData as $crypto): ?>
                    <tr>
                        <td><?php echo $crypto['name']; ?></td>
                        <td><?php echo $crypto['name_abreviation']; ?></td>
                        <td><?php echo '$' . number_format($crypto['price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<footer>
    &copy; 2024 Crypto Express. All Rights Reserved.
</footer>

</body>
</html>





