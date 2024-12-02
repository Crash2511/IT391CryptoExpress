<?php
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Leaderboard table styles */
        #leaderboard-table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        #leaderboard-table th, #leaderboard-table td {
            padding: 15px;
            text-align: center;
        }

        #leaderboard-table th {
            background-color: #343a40;
            color: white;
            text-transform: uppercase;
            cursor: pointer; /* Make headers clickable */
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

        /* Style for buttons */
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }

        .action-buttons button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin: 5px;
        }

        .action-buttons button:hover {
            background-color: #218838;
        }

        /* Navigation bar styles */
        header nav {
            background-color: #333;
            padding: 15px;
        }

        header nav h1 a {
            color: white;
            text-decoration: none;
            font-size: 24px;
        }

        .main-nav, .nav-right {
            list-style-type: none;
            padding: 0;
        }

        .main-nav li, .nav-right li {
            display: inline;
            margin-right: 20px;
        }

        .main-nav a, .nav-right a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        .main-nav a:hover, .nav-right a:hover {
            text-decoration: underline;
        }

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

    <h2>Leaderboard - Crypto Express</h2>

    <!-- Leaderboard Table -->
    <table id="leaderboard-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Account Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leaderboardData as $index => $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                <td><?php echo htmlspecialchars($row['account_balance']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <footer>
        &copy; 2024 Crypto Express. All rights reserved.
    </footer>
</body>
</html>



