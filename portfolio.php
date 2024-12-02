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
} else {
    // Message to display when user is not logged in
    $message = "Your portfolio will not be filled until you log in and start using the website.";
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
        <section id="portfolio-overview">
            <h2>Your Portfolio</h2>
            <?php if (isset($message)) { ?>
                <div class="message"><?php echo $message; ?></div>
            <?php } ?>

            <!-- Portfolio Table -->
            <table>
                <thead>
                    <tr>
                        <th>Crypto Name</th>
                        <th>Abbreviation</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($portfolioData as $item) { ?>
                        <tr>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['name_abreviation']; ?></td>
                            <td><?php echo $item['amount']; ?></td>
                            <td><?php echo "$" . number_format($item['price'], 2); ?></td>
                            <td><?php echo "$" . number_format($item['amount'] * $item['price'], 2); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Graph Section -->
            <div class="chart-container">
                <canvas id="cryptoChart"></canvas>
            </div>

            <script>
                var ctx = document.getElementById('cryptoChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ["1d", "1m", "1y"],
                        datasets: [{
                            label: 'Crypto Price History',
                            data: [/* Add your data here */],
                            borderColor: '#2ecc71',
                            backgroundColor: 'rgba(46, 204, 113, 0.2)',
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            </script>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Crypto Express. All rights reserved.</p>
    </footer>
</body>
</html>


