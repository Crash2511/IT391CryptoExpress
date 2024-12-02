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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Basic styling for tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .message {
            text-align: center;
            margin: 20px;
            font-size: 1.2rem;
            color: #e74c3c;
        }
        .chart-container {
            width: 80%;
            margin: 0 auto;
        }
        /* Footer styling */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .crypto-table {
            margin-top: 20px;
            width: 100%;
            text-align: center;
        }

        .crypto-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .crypto-table th, .crypto-table td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        .crypto-table th {
            background-color: #f4f4f4;
        }

        .crypto-table td {
            font-size: 1.1rem;
        }

    </style>
</head>
<body>
    <!-- Header with navigation -->
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

    <!-- Market Overview -->
    <main class="market-overview">
        <h2>Cryptocurrency Market Overview</h2>
        
        <!-- Table to display cryptocurrency data -->
        <div class="crypto-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Abbreviation</th>
                        <th>Price (USD)</th>
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

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Crypto Express. All Rights Reserved.</p>
    </footer>

</body>
</html>





