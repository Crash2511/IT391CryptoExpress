<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "user";
$password = "Battle2511!";
$dbname = "crypto_express";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cryptocurrency data from the crypto_information table
$sql = "SELECT name, name_abreviation AS symbol, price FROM crypto_information";
$result = $conn->query($sql);

$crypto_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $crypto_data[] = $row;
    }
}

// Handle buy/sell transactions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crypto_id = $_POST['crypto_id'];
    $amount = (float)$_POST['amount'];
    $transaction_type = $_POST['transaction_type']; // 'buy' or 'sell'
    $user_id = "example_user"; // Replace with actual user ID (from session or authentication)

    // Fetch the current price of the selected cryptocurrency
    $stmt = $conn->prepare("SELECT price FROM crypto_information WHERE name_abreviation = ?");
    $stmt->bind_param("s", $crypto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $crypto = $result->fetch_assoc();
    $price = (float)$crypto['price'];
    $stmt->close();

    // Calculate the total transaction value
    $total_value = $price * $amount;

    // Handle the transaction (buy or sell)
    if ($transaction_type === 'buy') {
        // Deduct total value from the user's account balance and add the crypto to their portfolio
        $conn->begin_transaction();
        try {
            // Deduct balance
            $stmt = $conn->prepare("UPDATE user_information SET account_balance = account_balance - ? WHERE user_id = ?");
            $stmt->bind_param("ds", $total_value, $user_id);
            $stmt->execute();

            // Add to portfolio
            $stmt = $conn->prepare("INSERT INTO portfolio_information (user_id, crypto_id, amount) 
                                     VALUES (?, ?, ?) 
                                     ON DUPLICATE KEY UPDATE amount = amount + VALUES(amount)");
            $stmt->bind_param("ssd", $user_id, $crypto_id, $amount);
            $stmt->execute();

            $conn->commit();
            $message = "Successfully purchased $amount of $crypto_id.";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Transaction failed: " . $e->getMessage();
        }
    } elseif ($transaction_type === 'sell') {
        // Deduct the crypto amount from the portfolio and add the total value to the user's balance
        $conn->begin_transaction();
        try {
            // Check if the user has enough of the crypto to sell
            $stmt = $conn->prepare("SELECT amount FROM portfolio_information WHERE user_id = ? AND crypto_id = ?");
            $stmt->bind_param("ss", $user_id, $crypto_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $portfolio = $result->fetch_assoc();
            $stmt->close();

            if ($portfolio && $portfolio['amount'] >= $amount) {
                // Deduct the crypto from the portfolio
                $stmt = $conn->prepare("UPDATE portfolio_information SET amount = amount - ? WHERE user_id = ? AND crypto_id = ?");
                $stmt->bind_param("dss", $amount, $user_id, $crypto_id);
                $stmt->execute();

                // Add the value to the user's account balance
                $stmt = $conn->prepare("UPDATE user_information SET account_balance = account_balance + ? WHERE user_id = ?");
                $stmt->bind_param("ds", $total_value, $user_id);
                $stmt->execute();

                $conn->commit();
                $message = "Successfully sold $amount of $crypto_id.";
            } else {
                $message = "Insufficient $crypto_id in portfolio to sell.";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Transaction failed: " . $e->getMessage();
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
    <style>
        .favorite-btn {
            padding: 5px 10px;
            background-color: #f1c40f;
            border: none;
            cursor: pointer;
            margin-top: 5px;
        }

        .favorite-btn.active {
            background-color: #e67e22;
        }

        .crypto-item {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }

        .crypto-item p {
            font-size: 1em;
        }

        .action-btns {
            margin-top: 10px;
        }

        .action-btns button {
            margin: 0 5px;
        }

        .transaction-form {
            margin: 20px 0;
            text-align: center;
        }

        .transaction-form input, .transaction-form select, .transaction-form button {
            margin: 5px;
        }

        .message {
            color: green;
            font-weight: bold;
            text-align: center;
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
        </nav>
    </header>

    <main>
        <section id="market-overview">
            <h2>Market Overview</h2>
            <div id="crypto-list">
                <!-- PHP will populate the cryptocurrency list -->
                <?php foreach ($crypto_data as $crypto) { ?>
                    <div class="crypto-item">
                        <h3><?php echo $crypto['name']; ?> (<?php echo $crypto['symbol']; ?>)</h3>
                        <p>Price: $<?php echo number_format($crypto['price'], 2); ?></p>
                        <div class="action-btns">
                            <button class="favorite-btn" onclick="toggleFavorite('<?php echo $crypto['symbol']; ?>')">Favorite</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>

        <section id="trade">
            <h2>Trade Cryptocurrency</h2>
            <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>
            <form class="transaction-form" method="POST" action="market.php">
                <label for="crypto_id">Cryptocurrency:</label>
                <select id="crypto_id" name="crypto_id" required>
                    <?php foreach ($crypto_data as $crypto) { ?>
                        <option value="<?php echo $crypto['symbol']; ?>"><?php echo $crypto['name']; ?> (<?php echo $crypto['symbol']; ?>)</option>
                    <?php } ?>
                </select>
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.00000001" min="0" required>
                <button type="submit" name="transaction_type" value="buy">Buy</button>
                <button type="submit" name="transaction_type" value="sell">Sell</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>

    <script>
        const favorites = JSON.parse(localStorage.getItem('favoriteCryptos')) || [];

        function toggleFavorite(symbol) {
            const index = favorites.indexOf(symbol);
            if (index > -1) {
                favorites.splice(index, 1);
            } else {
                favorites.push(symbol);
            }
            localStorage.setItem('favoriteCryptos', JSON.stringify(favorites));
        }
    </script>
</body>
</html>




