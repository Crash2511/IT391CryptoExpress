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

// Fetch portfolio data
$sql = "SELECT name, name_abreviation, amount, price FROM portfolio_information INNER JOIN crypto_information ON portfolio_information.crypto_id = crypto_information.id WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$portfolioData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $portfolioData[] = $row;
    }
}
$stmt->close();

// Fetch trade history
$sql = "SELECT crypto_information.name, crypto_information.name_abreviation, transaction_type, amount, price, timestamp FROM transaction_history INNER JOIN crypto_information ON transaction_history.crypto_id = crypto_information.id WHERE user_id = ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$tradeData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tradeData[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <section id="portfolio">
            <h2>Your Portfolio (Detailed)</h2>
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
        </section>

        <section id="last-trades">
            <h2>Last Trades</h2>
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
        </section>

        <section id="portfolio-graph">
            <h2>Your Portfolio Performance</h2>
            <canvas id="winsChart" width="300" height="150"></canvas>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>

    <script>
        const ctx = document.getElementById('winsChart').getContext('2d');
        const winsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Portfolio Value Over Time',
                    data: [12000, 15000, 18000, 17000, 19000, 20000, 21000],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Portfolio Performance Over Time'
                    }
                }
            }
        });
    </script>
</body>
</html>
