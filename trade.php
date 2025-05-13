<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $crypto = $_POST['crypto'];
    $amount = floatval($_POST['amount']);
    $action = $_POST['action'];

    // Fetch current price of the selected cryptocurrency
    $sql = "SELECT price FROM crypto_information WHERE name_abreviation = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $crypto);
        $stmt->execute();
        $result = $stmt->get_result();
        $cryptoPrice = 0;

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $cryptoPrice = floatval($row['price']);
        }
        $stmt->close();
    }

    if ($cryptoPrice > 0) {
        if ($action == 'buy') {
            // Insert or update portfolio
            $sql = "INSERT INTO portfolio_information (user_id, crypto_id, amount) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE amount = amount + ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssdd", $userId, $crypto, $amount, $amount);
                $stmt->execute();
                $stmt->close();
            }

            // Insert transaction history
            $sql = "INSERT INTO transaction_history (user_id, trading_pair, transaction_type, amount, price, timestamp) VALUES (?, ?, 'buy', ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssdd", $userId, $crypto, $amount, $cryptoPrice);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($action == 'sell') {
            // Check if the user has enough of the cryptocurrency to sell
            $sql = "SELECT amount FROM portfolio_information WHERE user_id = ? AND crypto_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ss", $userId, $crypto);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $currentAmount = floatval($row['amount']);

                    if ($currentAmount >= $amount) {
                        // Update portfolio
                        $sql = "UPDATE portfolio_information SET amount = amount - ? WHERE user_id = ? AND crypto_id = ?";
                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            $stmt->bind_param("dss", $amount, $userId, $crypto);
                            $stmt->execute();
                            $stmt->close();
                        }

                        // Insert transaction history
                        $sql = "INSERT INTO transaction_history (user_id, trading_pair, transaction_type, amount, price, timestamp) VALUES (?, ?, 'sell', ?, ?, NOW())";
                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            $stmt->bind_param("ssdd", $userId, $crypto, $amount, $cryptoPrice);
                            $stmt->execute();
                            $stmt->close();
                        }
                    } else {
                        echo "<p>Not enough balance to sell.</p>";
                    }
                }
                $stmt->close();
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trade - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
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
        <section id="trade">
            <h2>Buy/Sell Cryptocurrency</h2>
            <form action="trade.php" method="post">
                <label for="crypto">Select Cryptocurrency:</label>
                <select id="crypto" name="crypto">
                    <?php
                    // Fetch available cryptocurrencies
                    $conn = new mysqli($servername, $username, $password, $dbname);
                    $sql = "SELECT name, name_abreviation FROM crypto_information";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['name_abreviation']) . "'>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['name_abreviation']) . ")</option>";
                        }
                    }
                    $conn->close();
                    ?>
                </select>

                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" min="0.01" step="0.01" required>

                <button type="submit" name="action" value="buy">Buy</button>
                <button type="submit" name="action" value="sell">Sell</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>
</body>
</html>
