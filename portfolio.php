<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "user"; 
$password = "Battle2511!";  // Replace with your password 
$dbname = "crypto_express";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch portfolio data from the crypto_information table
$sql = "SELECT * FROM crypto_information";  // Changed to your table
$result = $conn->query($sql);

$portfolio_data = [];
if ($result->num_rows > 0) {
    // Fetch each row and add to the portfolio_data array
    while ($row = $result->fetch_assoc()) {
        $portfolio_data[] = [
            'asset' => $row['asset'],
            'amount' => $row['amount'],
            'price' => $row['price']
        ];
    }
} else {
    echo "0 results";  // This will display if no data is returned from the database
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Portfolio - Crypto Express</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link your CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js -->
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
                    // Check if portfolio_data is populated and create table rows dynamically
                    $total_value = 0;  // To calculate total portfolio value
                    if (count($portfolio_data) > 0) {
                        foreach ($portfolio_data as $portfolio_item) {
                            $asset = $portfolio_item['asset'];
                            $amount = $portfolio_item['amount'];
                            $price = $portfolio_item['price'];
                            $value = $amount * $price; // Calculate the portfolio value
                            $total_value += $value;
                            echo "<tr>
                                    <td>{$asset}</td>
                                    <td class='amount'>{$amount}</td>
                                    <td class='price'>{$price}</td>
                                    <td class='value'>{$value}</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No portfolio data available.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <div id="portfolio-summary">
                <h3>Total Portfolio Value: <span id="total-value" class="value">$<?php echo number_format($total_value, 2); ?></span></h3>
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
                    <!-- JavaScript will populate this -->
                </tbody>
            </table>
        </section>

        <section id="portfolio-graph">
            <h2>Your Portfolio Performance</h2>
            <label for="crypto-filter">Select Cryptocurrency:</label>
            <select id="crypto-filter" onchange="updateCryptoChart()">
                <option value="all">All</option>
                <option value="BTC">Bitcoin (BTC)</option>
                <option value="ETH">Ethereum (ETH)</option>
                <option value="ADA">Cardano (ADA)</option>
            </select>

            <div class="time-range-buttons">
                <button onclick="updateChart('1d')">1D</button>
                <button onclick="updateChart('1w')">1W</button>
                <button onclick="updateChart('1m')">1M</button>
            </div>

            <canvas id="portfolioChart"></canvas>
        </section>
    </main>

    <script>
        // Initialize the portfolio chart
        const ctx = document.getElementById('portfolioChart').getContext('2d');
        const portfolioChart = new Chart(ctx, {
            type: 'line',  // Choose chart type (line, bar, etc.)
            data: {
                labels: [],  // Time labels (e.g., days, weeks)
                datasets: [{
                    label: 'Portfolio Value',
                    data: [],  // Portfolio value data
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Function to update the chart based on selected cryptocurrency
        function updateCryptoChart() {
            const cryptoFilter = document.getElementById('crypto-filter').value;
            const chartData = { /* Sample data or fetch data based on filter */ };
            if (cryptoFilter === "all") {
                // You can fetch data for all assets or display aggregated data
            } else {
                // Fetch and update data for specific cryptocurrency
            }
        }

        // Function to update the chart with specific time range (1d, 1w, 1m)
        function updateChart(range) {
            const data = {
                '1d': [100, 150, 120, 180, 200],  // Sample data for 1-day range
                '1w': [150, 120, 180, 200, 170],  // Sample data for 1-week range
                '1m': [120, 180, 200, 170, 180]   // Sample data for 1-month range
            };

            portfolioChart.data.labels = ['10 AM', '11 AM', '12 PM', '1 PM', '2 PM'];  // Sample labels
            portfolioChart.data.datasets[0].data = data[range];
            portfolioChart.update();
        }
    </script>
</body>
</html>



