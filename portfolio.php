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
            text-align: right;
        }
        .buy-action {
            background-color: #e6ffe6;
            color: green;
        }
        .sell-action {
            background-color: #ffe6e6;
            color: red;
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
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="portfolio">
            <h2>Your Portfolio (Detailed)</h2>
            <?php if (empty($portfolioData)): ?>
                <p>Your portfolio is currently empty. Once you start adding assets, they will appear here.</p>
            <?php else: ?>
                <table id="portfolio-table">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th class="amount">Amount</th>
                            <th class="price">Price</th>
                            <th class="value">Value</th>
                        </tr>
                    </thead>
                    <tbody id="portfolio-list">
                        <?php 
                        $totalValue = 0;
                        foreach ($portfolioData as $asset): 
                            $assetValue = $asset['amount'] * $asset['price'];
                            $totalValue += $assetValue;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($asset['name']) ?> (<?= htmlspecialchars($asset['name_abreviation']) ?>)</td>
                                <td class="amount"><?= number_format($asset['amount'], 2) ?></td>
                                <td class="price">$<?= number_format($asset['price'], 2) ?></td>
                                <td class="value">$<?= number_format($assetValue, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="portfolio-summary">
                    <h3>Total Portfolio Value: <span id="total-value" class="value">$<?= number_format($totalValue, 2) ?></span></h3>
                </div>
            <?php endif; ?>
        </section>

        <section id="last-trades">
            <h2>Last Trades</h2>
            <?php if (empty($tradeData)): ?>
                <p>No trades found. Your trade history will be displayed here once you start trading.</p>
            <?php else: ?>
                <table id="trades-table">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Action</th>
                            <th class="amount">Amount</th>
                            <th class="price">Price</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="trades-list">
                        <?php foreach ($tradeData as $trade): ?>
                            <tr>
                                <td><?= htmlspecialchars($trade['name']) ?> (<?= htmlspecialchars($trade['name_abreviation']) ?>)</td>
                                <td class="<?= $trade['transaction_type'] === 'buy' ? 'buy-action' : 'sell-action' ?>">
                                    <?= ucfirst(htmlspecialchars($trade['transaction_type'])) ?>
                                </td>
                                <td class="amount"><?= number_format($trade['amount'], 2) ?></td>
                                <td class="price">$<?= number_format($trade['price'], 2) ?></td>
                                <td><?= htmlspecialchars($trade['timestamp']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section id="growth-chart">
            <h2>Portfolio Growth Over Time</h2>
            <canvas id="growthChart"></canvas>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>

    <script>
        // Example data for the growth chart (replace with actual data from your server if available)
        const labels = ["January", "February", "March", "April", "May", "June"];
        const data = {
            labels: labels,
            datasets: [{
                label: 'Portfolio Value Over Time',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                data: [500, 700, 800, 1200, 1500, 1800], // Replace with dynamic values
                fill: true
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Portfolio Growth Over Time'
                    }
                }
            }
        };

        var growthChart = new Chart(
            document.getElementById('growthChart'),
            config
        );
    </script>
</body>
</html>
