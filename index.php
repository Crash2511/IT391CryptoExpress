<?php
// Database connection
// Database connection settings
$servername = "localhost";
$username = "user";
$password = ""; // your password here
$dbname = "crypto_express"; // Edit db if necessary here

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Simulator</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        <?php include 'styles.css'; ?>
        .summary-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .summary-item {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #ddd;
            text-align: center;
            border-radius: 8px;
        }
        table.stock-chart {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table.stock-chart th, table.stock-chart td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table.stock-chart th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1><a href="index.php" style="color: white; text-decoration: none;">Crypto Express</a></h1>
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
            <?php
            // Show logout button only if the user is logged in
            if (isset($_SESSION['user_id'])) {
                echo '<form method="POST" action="logout.php">
                        <button type="submit" name="logout" class="logout-button">Logout</button>
                      </form>';
            }
            ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="summary-container">
            <div class="summary-item">
                <h3>Total Coins</h3>
                <p id="total-coins">0</p>
            </div>
            <div class="summary-item">
                <h3>Total Value (USD)</h3>
                <p id="total-value">$0.00</p>
            </div>
            <div class="summary-item">
                <h3>Wins</h3>
                <p id="win-count">0</p>
            </div>
            <div class="summary-item">
                <h3>Losses</h3>
                <p id="loss-count">0</p>
            </div>
        </section>

        <section id="market-overview">
            <h2>Market Overview</h2>
            <table class="stock-chart">
                <thead>
                    <tr>
                        <th>Cryptocurrency</th>
                        <th>Price (USD)</th>
                        <th>Change (USD)</th>
                        <th>Change (%)</th>
                        <th>Market Cap</th>
                        <th>Volume</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM crypto_information";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['name_abreviation']) . ")</td>
                                <td>$" . htmlspecialchars($row['price']) . "</td>
                                <td>$" . htmlspecialchars($row['price_change']) . "</td>
                                <td>" . htmlspecialchars($row['change_percent']) . "%</td>
                                <td>" . htmlspecialchars($row['market_cap']) . "</td>
                                <td>" . htmlspecialchars($row['volume']) . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No data available.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Portfolio Section in Index Page -->
        <section id="index-portfolio">
            <h2>Your Portfolio (Summary)</h2>
            <div id="portfolio-list-index">
                <table class="portfolio-summary-table">
                    <thead>
                        <tr>
                            <th>Cryptocurrency</th>
                            <th>Amount</th>
                            <th>Value (USD)</th>
                            <th>Bought Price (USD)</th>
                            <th>Current Price (USD)</th>
                        </tr>
                    </thead>
                    <tbody id="portfolio-table-body">
                        <!-- Portfolio Data could be queried and populated similarly -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Buy/Sell Actions -->
        <section id="actions">
            <h2>Buy/Sell</h2>
            <form id="trade-form" action="trade.php" method="post">
                <label for="crypto">Select Cryptocurrency:</label>
                <select id="crypto" name="crypto">
                    <?php
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['name_abreviation']) . "'>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['name_abreviation']) . ")</option>";
                        }
                    }
                    ?>
                </select>

                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" min="1">

                <button type="submit" name="action" value="buy">Buy</button>
                <button type="submit" name="action" value="sell">Sell</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>

    <script>
        function calculateSummary() {
            let totalCoins = 0;
            let totalValue = 0;
            let wins = 0;
            let losses = 0;

            // Assuming this data will be fetched from PHP as JSON via Ajax in the future
            const portfolioData = [
                { name: 'Bitcoin', symbol: 'BTC', amount: 0.5, price: 34000.00, boughtPrice: 30000.00 },
                { name: 'Ethereum', symbol: 'ETH', amount: 5, price: 1900.00, boughtPrice: 1800.00 },
            ];

            portfolioData.forEach(asset => {
                totalCoins += asset.amount;
                totalValue += asset.amount * asset.price;
                if (asset.price > asset.boughtPrice) {
                    wins++;
                } else if (asset.price < asset.boughtPrice) {
                    losses++;
                }
            });

            document.getElementById('total-coins').textContent = totalCoins.toFixed(2);
            document.getElementById('total-value').textContent = `$${totalValue.toFixed(2)}`;
            document.getElementById('win-count').textContent = wins;
            document.getElementById('loss-count').textContent = losses;
        }

        window.onload = function() {
            calculateSummary();
        };
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

