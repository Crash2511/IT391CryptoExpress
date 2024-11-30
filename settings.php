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

// Load user settings from the database
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM user_information WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
    $stmt->close();
}

// Update user settings in the database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leaderboard = isset($_POST['leaderboard']) ? 1 : 0;
    $alias = $_POST['alias'];
    $currency = $_POST['currency'];
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    $language = $_POST['language'];
    $theme = $_POST['theme'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "UPDATE user_information SET alias = ?, preferred_currency = ?, notifications = ?, language = ?, theme = ?, email = ?, username = ?, user_password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissssss", $alias, $currency, $notifications, $language, $theme, $email, $username, $password, $user_id);

    if ($stmt->execute()) {
        echo "Settings saved successfully!";
    } else {
        echo "Error saving settings: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* General body styling */
        body.light-theme {
            background-color: #f0f0f0;
            color: #000;
        }
        body.dark-theme {
            background-color: #1e1e1e;
            color: #fff;
        }

        /* Container styling for the settings card */
        .settings-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        /* Style for the settings items */
        .settings-item {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .settings-item label {
            font-size: 1.1rem;
            font-weight: 600;
            flex: 1;
        }

        .settings-item select,
        .settings-item input[type="checkbox"],
        .settings-item input[type="text"],
        .settings-item input[type="password"],
        .settings-item input[type="email"] {
            flex: 0.4;
            padding: 5px;
        }

        /* Save button styling */
        .save-button {
            display: block;
            margin: 40px auto 0;
            padding: 10px 20px;
            background-color: #343a40;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            max-width: 200px;
        }

        .save-button:hover {
            background-color: #495057;
        }

        /* Header styling */
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }

    </style>
</head>
<body class="<?php echo isset($user['theme']) ? $user['theme'] . '-theme' : 'light-theme'; ?>">
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
        <div class="settings-container">
            <h2>Settings</h2>

            <form method="post" action="">
                <!-- Leaderboard toggle -->
                <div class="settings-item">
                    <label for="leaderboard-toggle">Leaderboard:</label>
                    <input type="checkbox" id="leaderboard-toggle" name="leaderboard" <?php echo isset($user['leaderboard']) && $user['leaderboard'] ? 'checked' : ''; ?>>
                </div>

                <!-- Alias -->
                <div class="settings-item">
                    <label for="alias-input">Alias:</label>
                    <input type="text" id="alias-input" name="alias" value="<?php echo $user['alias']; ?>">
                </div>

                <!-- Currency -->
                <div class="settings-item">
                    <label for="currency-select">Preferred Currency:</label>
                    <select id="currency-select" name="currency">
                        <option value="USD" <?php echo $user['preferred_currency'] == 'USD' ? 'selected' : ''; ?>>USD</option>
                        <option value="EUR" <?php echo $user['preferred_currency'] == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                        <option value="BTC" <?php echo $user['preferred_currency'] == 'BTC' ? 'selected' : ''; ?>>BTC</option>
                        <option value="ETH" <?php echo $user['preferred_currency'] == 'ETH' ? 'selected' : ''; ?>>ETH</option>
                    </select>
                </div>

                <!-- Notifications and Email Alerts grouped -->
                <div class="settings-item">
                    <label for="notifications-toggle">Notifications and Email Alerts:</label>
                    <input type="checkbox" id="notifications-toggle" name="notifications" <?php echo isset($user['notifications']) && $user['notifications'] ? 'checked' : ''; ?>>
                </div>

                <!-- Language -->
                <div class="settings-item">
                    <label for="language-select">Language:</label>
                    <select id="language-select" name="language">
                        <option value="en" <?php echo $user['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="es" <?php echo $user['language'] == 'es' ? 'selected' : ''; ?>>Spanish</option>
                        <option value="fr" <?php echo $user['language'] == 'fr' ? 'selected' : ''; ?>>French</option>
                        <option value="de" <?php echo $user['language'] == 'de' ? 'selected' : ''; ?>>German</option>
                    </select>
                </div>

                <!-- Theme -->
                <div class="settings-item">
                    <label for="theme-select">Theme:</label>
                    <select id="theme-select" name="theme">
                        <option value="light" <?php echo $user['theme'] == 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?php echo $user['theme'] == 'dark' ? 'selected' : ''; ?>>Dark</option>
                    </select>
                </div>

                <!-- Email change and confirmation -->
                <div class="settings-item">
                    <label for="email-input">Change Email:</label>
                    <input type="email" id="email-input" name="email" value="<?php echo $user['email']; ?>">
                </div>

                <!-- Username -->
                <div class="settings-item">
                    <label for="username-input">Username:</label>
                    <input type="text" id="username-input" name="username" value="<?php echo $user['username']; ?>">
                </div>

                <!-- Password -->
                <div class="settings-item">
                    <label for="password-input">New Password:</label>
                    <input type="password" id="password-input" name="password" placeholder="New password">
                </div>

                <!-- Save button -->
                <button class="save-button" type="submit">Save Settings</button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>
</body>
</html>



