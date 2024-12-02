<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$servername = "localhost";
$username = "user";
$password = "Battle2511!";
$dbname = "crypto_express";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize empty arrays for portfolio and trade data
$portfolioData = [];
$tradeData = [];

// Fetch portfolio data if the user is logged in
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT crypto_information.name, crypto_information.name_abreviation, portfolio_information.amount, crypto_information.price FROM portfolio_information INNER JOIN crypto_information ON portfolio_information.crypto_id = crypto_information.name WHERE portfolio_information.user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $portfolioData[] = $row;
            }
        }
        $stmt->close();
    }

    // Fetch trade history
    $sql = "SELECT crypto_information.name, crypto_information.name_abreviation, transaction_history.transaction_type, transaction_history.amount, transaction_history.price, transaction_history.timestamp FROM transaction_history INNER JOIN crypto_information ON transaction_history.trading_pair = crypto_information.name_abreviation WHERE transaction_history.user_id = ? ORDER BY transaction_history.timestamp DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tradeData[] = $row;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Basic styling for tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
        .amount, .price, .value {
            font-weight: bold;
        }

        /* Black bar at the bottom */
        footer {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        footer a {
            color: #fff;
            text-decoration: none;
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Your Portfolio</h2>

        <?php if (isset($_SESSION['user_id'])): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Abbreviation</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($portfolioData as $crypto): ?>
                        <tr>
                            <td><?php echo $crypto['name']; ?></td>
                            <td><?php echo $crypto['name_abreviation']; ?></td>
                            <td><?php echo $crypto['amount']; ?></td>
                            <td><?php echo $crypto['price']; ?></td>
                            <td><?php echo $crypto['amount'] * $crypto['price']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Trade History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Crypto</th>
                        <th>Transaction Type</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tradeData as $trade): ?>
                        <tr>
                            <td><?php echo $trade['name']; ?></td>
                            <td><?php echo $trade['transaction_type']; ?></td>
                            <td><?php echo $trade['amount']; ?></td>
                            <td><?php echo $trade['price']; ?></td>
                            <td><?php echo $trade['timestamp']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Please <a href="login.php">login</a> to view your portfolio and trade history.</p>
        <?php endif; ?>
    </main>

    <!-- Footer with black bar and copyright symbol -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Crypto Express. All rights reserved.</p>
    </footer>
</body>
</html>

