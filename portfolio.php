<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "your_password";
$dbname = "crypto_express";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch portfolio data
$sql = "SELECT * FROM portfolio_data";  // Change to your actual portfolio table
$result = $conn->query($sql);

$portfolio_data = [];
if ($result->num_rows > 0) {
    // Fetch each row and add to the portfolio_data array
    while($row = $result->fetch_assoc()) {
        $portfolio_data[] = [
            'asset' => $row['asset'],
            'amount' => $row['amount'],
            'price' => $row['price']
        ];
    }
} else {
    echo "0 results";
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
            <h1><a href="index.html">Crypto Express</a></h1>
            <ul class="main-nav">
                <li><a href="index.html">Home</a></li>
                <li><a href="portfolio.html">Portfolio</a></li>
                <li><a href="market.html">Market</a></li>
                <li><a href="leaderboard.html">Leaderboard</a></li>
                <li><a href="settings.html">Settings</a></li>
            </ul>
            <ul class="nav-right">
                <li><a href="login.html">Login</a></li>
                <li><a href="register.html">Register</a></li>
                <li><a href="add-currency.html" class="add-currency-link">Add Currency</a></li>
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
                    // Generate portfolio table rows dynamically
                    foreach ($portfolio_data as $portfolio_item) {
                        $asset = $portfolio_item['asset'];
                        $amount = $portfolio_item['amount'];
                        $price = $portfolio_item['price']; // Assuming this is fetched from your table
                        $value = $amount * $price; // Calculate total value
                        echo "<tr>
                                <td>{$asset}</td>
                                <td class='amount'>{$amount}</td>
                                <td class='price'>{$price}</td>
                                <td class='value'>{$value}</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <div id="portfolio-summary">
                <h3>Total Portfolio Value: <span id="total-value" class="value">$0.00</span></h3>
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
                    <!-- JavaScript will populate this with trades -->
                </tbody>
            </table>
        </section>

        <section id="portfolio-graph">
            <h2>Your Portfolio Performance</h2>

            <!-- Dropdown to select cryptocurrency -->
            <label for="crypto-filter">Select Cryptocurrency:</label>
            <select id="crypto-filter" onchange="updateCryptoChart()">
                <option value="all">All</option>
                <option value="BTC">Bitcoin (BTC)</option>
                <option value="ETH">Ethereum (ETH)</option>
                <option value="ADA">Cardano (ADA)</option>
            </select>

            <!-- Time range buttons -->
            <div class="time-range-buttons">
                <button onclick="updateChart('1d')">1D</button>
                <button onclick="updateChart('1w')">1W</button>
                <button onclick="updateChart('1m')">1M</button>
                <button onclick="updateChart('3m')">3M</button>
                <button onclick="updateChart('1y')">1Y</button>
            </div>

            <canvas id="portfolioChart"></canvas>
        </section>
    </main>

    <script>
        const ctx = document.getElementById('portfolioChart').getContext('2d');
        let portfolioChart;

        // Placeholder function to simulate getting historical data
        function fetchCryptoData(crypto, timeRange) {
            // This can be replaced with an actual API call to fetch historical data for the selected crypto and time range.
            const data = {
                labels: ["2021-01", "2021-02", "2021-03", "2021-04", "2021-05"], // Example time labels
                datasets: [{
                    label: crypto + ' Price',
                    data: [100, 120, 140, 130, 160], // Example price data (replace with actual data)
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1
                }]
            };
            return data;
        }

        // Update chart based on selected cryptocurrency
        function updateCryptoChart() {
            const selectedCrypto = document.getElementById('crypto-filter').value;
            const timeRange = '1w'; // Default to 1 week, could be dynamic based on user selection

            const data = fetchCryptoData(selectedCrypto, timeRange);
            
            if (portfolioChart) {
                portfolioChart.destroy();
            }

            portfolioChart = new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                });
        }

        // Update chart based on time range
        function updateChart(timeRange) {
            const selectedCrypto = document.getElementById('crypto-filter').value;
            const data = fetchCryptoData(selectedCrypto, timeRange);

            if (portfolioChart) {
                portfolioChart.destroy();
            }

            portfolioChart = new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                });
        }

        // Initial chart render with default values
        window.onload = function() {
            updateCryptoChart();
        }
    </script>
</body>
</html>


