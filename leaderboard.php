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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        /* Style for mobile responsiveness */
        @media (max-width: 768px) {
            #leaderboard-table {
                width: 100%;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1><a href="index.php">Crypto Express</a></h1>
            <ul class="main-nav">
                <li><a href="index.php">Home</a></li>
                <li><a href="leaderboard.php">Leaderboard</a></li>
                <!-- Add other menu items as necessary -->
            </ul>
        </nav>
    </header>

    <h2>Leaderboard</h2>

    <table id="leaderboard-table">
        <thead>
            <tr>
                <th onclick="sortTable(0)">User ID <span class="sort-arrow">↑↓</span></th>
                <th onclick="sortTable(1)">Account Balance <span class="sort-arrow">↑↓</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leaderboardData as $index => $data): ?>
                <tr>
                    <td><?php echo htmlspecialchars($data['user_id']); ?></td>
                    <td><?php echo number_format($data['account_balance'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <footer>
        <p>&copy; 2024 Crypto Express. All rights reserved.</p>
    </footer>

    <script>
        // Function to sort the table by columns
        let sortOrder = [true, true]; // Store sorting order for each column

        function sortTable(colIndex) {
            const table = document.getElementById("leaderboard-table");
            const rows = Array.from(table.rows).slice(1); // Skip header row
            const isNumeric = colIndex === 1;

            rows.sort((a, b) => {
                const aText = a.cells[colIndex].innerText;
                const bText = b.cells[colIndex].innerText;

                if (isNumeric) {
                    const aVal = parseFloat(aText.replace(/,/g, ''));
                    const bVal = parseFloat(bText.replace(/,/g, ''));
                    return sortOrder[colIndex] ? bVal - aVal : aVal - bVal;
                } else {
                    return sortOrder[colIndex] ? aText.localeCompare(bText) : bText.localeCompare(aText);
                }
            });

            // Append sorted rows back to the table body
            rows.forEach(row => table.tBodies[0].appendChild(row));
            sortOrder[colIndex] = !sortOrder[colIndex]; // Toggle sorting direction
        }
    </script>
</body>
</html>



