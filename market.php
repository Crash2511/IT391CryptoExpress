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

// Handle Buy/Sell Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crypto_id = $_POST['crypto_id'];
    $transaction_type = $_POST['transaction_type']; // 'buy' or 'sell'
    $amount = $_POST['amount'];

    // Fetch user's current portfolio for the selected cryptocurrency
    $sql = "SELECT amount FROM portfolio_information WHERE user_id = ? AND crypto_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $_SESSION['user_id'], $crypto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_crypto = $result->fetch_assoc();
    $stmt->close();

    // Process Buy Action
    if ($transaction_type == 'buy') {
        if ($amount > 0) {
            // Update portfolio to add the bought crypto
            if ($user_crypto) {
                // If user already has this crypto, just update the amount
                $new_amount = $user_crypto['amount'] + $amount;
                $sql = "UPDATE portfolio_information SET amount = ? WHERE user_id = ? AND crypto_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("dss", $new_amount, $_SESSION['user_id'], $crypto_id);
                $stmt->execute();
                $stmt->close();
            } else {
                // If user doesn't have this crypto, insert new row
                $sql = "INSERT INTO portfolio_information (user_id, crypto_id, amount) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssd", $_SESSION['user_id'], $crypto_id, $amount);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Process Sell Action
    if ($transaction_type == 'sell' && $user_crypto) {
        if ($user_crypto['amount'] >= $amount && $amount > 0) {
            // Update portfolio to subtract the sold crypto
            $new_amount = $user_crypto['amount'] - $amount;
            $sql = "UPDATE portfolio_information SET amount = ? WHERE user_id = ? AND crypto_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dss", $new_amount, $_SESSION['user_id'], $crypto_id);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "<p class='message'>Insufficient balance to sell.</p>";
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

    <main>
        <h2>Market Overview</h2>
        <div class="crypto-table">
            <table>
                <thead>
                    <tr>
                        <th>Cryptocurrency</th>
                        <th>Abbreviation</th>
                        <th>Price (USD)</th>
                        <th>Buy/Sell</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cryptoData as $crypto): ?>
                    <tr>
                        <td><?= htmlspecialchars($crypto['name']); ?></td>
                        <td><?= htmlspecialchars($crypto['name_abreviation']); ?></td>
                        <td>$<?= number_format($crypto['price'], 2); ?></td>
                        <td>
                            <!-- Buy/Sell Form -->
                            <form action="market.php" method="POST">
                                <input type="hidden" name="crypto_id" value="<?= htmlspecialchars($crypto['name']); ?>">
                                <input type="number" name="amount" placeholder="Amount" required>
                                <select name="transaction_type" required>
                                    <option value="buy">Buy</option>
                                    <option value="sell">Sell</option>
                                </select>
                                <button type="submit">Submit</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>
</body>
</html>





