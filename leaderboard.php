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

// Fetch leaderboard data
$sql = "SELECT user_id, account_balance FROM user_information ORDER BY account_balance DESC";
$result = $conn->query($sql);
$leaderboardData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $leaderboardData[] = $row;
    }
}
$conn->close();

// Rank users by their account balance
$rankedData = array_map(function($row, $index) {
    $row['rank'] = $index + 1;
    return $row;
}, $leaderboardData, array_keys($leaderboardData));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Navigation bar styles */
        nav {
            background-color: #343a40;
            padding: 10px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: space-around;
        }

        nav ul li {
            display: inline;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 10px;
            font-size: 18px;
        }

        nav ul li a:hover {
            background-color: #555;
        }

        /* Leaderboard table styles */
        #leaderboard-table {
            width: 80%;
            margin: 100px auto 20px; /* Adding top margin to avoid overlap with the nav bar */
            border-collapse: collapse;
        }

        #leaderboard-table th, #leaderboard-table td {
            padding: 15px;
            text-align: center;
            cursor: pointer; /* Make headers clickable */
        }

        #leaderboard-table th {
            background-color: #343a40;
            color: white;
            text-transform: uppercase;
        }

        #leaderboard-table tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        #leaderboard-table tbody tr:nth-child(even) {
            background-color: #e0e0e0;
        }

        /* Special styles for the top 3 ranks */
        #leaderboard-table tbody tr:nth-child(1) td {
            font-weight: bold;
            color: gold;
        }

        #leaderboard-table tbody tr:nth-child(2) td {
            font-weight: bold;
            color: silver;
        }

        #leaderboard-table tbody tr:nth-child(3) td {
            font-weight: bold;
            color: #cd7f32; /* Bronze */
        }

        /* Hover effect */
        #leaderboard-table tbody tr:hover {
            background-color: #ddd;
        }

        /* Style for the page title */
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header with Navigation Bar -->
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

    <!-- Leaderboard Table -->
    <h2>Leaderboard</h2>
    <table id="leaderboard-table">
        <thead>
            <tr>
                <th onclick="sortLeaderboard()">Rank</th>
                <th>User ID</th>
                <th>Account Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rankedData as $user) : ?>
                <tr>
                    <td><?php echo $user['rank']; ?></td>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo $user['account_balance']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- JavaScript to reset ranks when clicking the Rank column -->
    <script>
        function sortLeaderboard() {
            // Reload the page to reset ranks (this will clear the rank reset for simplicity)
            window.location.reload();
        }
    </script>
</body>
</html>



