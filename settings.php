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
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

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
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .settings-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .settings-item {
            margin-bottom: 20px;
        }

        /* Styling for input fields and textareas */
        input[type="text"], input[type="email"], input[type="password"], select, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fafafa;
        }

        textarea {
            resize: vertical;
            height: 100px;
        }

        input[type="checkbox"] {
            margin-top: 10px;
        }

        label {
            font-size: 1.1rem;
            color: #555;
        }

        /* Button styling */
        .btn-save {
            padding: 12px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            margin-top: 20px;
        }

        .btn-save:hover {
            background-color: #2ecc71;
        }

        /* Toggle switch for light/dark theme */
        .theme-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .theme-toggle label {
            font-size: 1.1rem;
        }

        .theme-toggle input[type="checkbox"] {
            position: relative;
            width: 50px;
            height: 24px;
            border-radius: 50px;
            background-color: #ddd;
            transition: 0.3s;
        }

        .theme-toggle input[type="checkbox"]:checked {
            background-color: #27ae60;
        }

        .theme-toggle input[type="checkbox"]:before {
            content: "";
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: white;
            transition: 0.3s;
        }

        .theme-toggle input[type="checkbox"]:checked:before {
            left: 28px;
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
            </ul>
        </nav>
    </header>

    <div class="settings-container">
        <h2>User Settings</h2>
        <form method="POST" action="">
            <!-- Alias Field -->
            <div>
                <label for="alias">Alias</label>
                <input type="text" id="alias" name="alias" value="<?php echo $user['alias']; ?>" required>
            </div>

            <!-- Preferred Currency -->
            <div>
                <label for="currency">Preferred Currency</label>
                <select id="currency" name="currency">
                    <option value="USD" <?php if ($user['preferred_currency'] == 'USD') echo 'selected'; ?>>USD</option>
                    <option value="EUR" <?php if ($user['preferred_currency'] == 'EUR') echo 'selected'; ?>>EUR</option>
                    <option value="BTC" <?php if ($user['preferred_currency'] == 'BTC') echo 'selected'; ?>>BTC</option>
                    <option value="ETH" <?php if ($user['preferred_currency'] == 'ETH') echo 'selected'; ?>>ETH</option>
                </select>
            </div>

            <!-- Notifications -->
            <div>
                <label for="notifications">Enable Notifications</label>
                <input type="checkbox" id="notifications" name="notifications" <?php if ($user['notifications']) echo 'checked'; ?>>
            </div>

            <!-- Language -->
            <div>
                <label for="language">Language</label>
                <select id="language" name="language">
                    <option value="en" <?php if ($user['language'] == 'en') echo 'selected'; ?>>English</option>
                    <option value="es" <?php if ($user['language'] == 'es') echo 'selected'; ?>>Spanish</option>
                    <option value="fr" <?php if ($user['language'] == 'fr') echo 'selected'; ?>>French</option>
                </select>
            </div>

            <!-- Theme -->
            <div>
                <label for="theme">Theme</label>
                <select id="theme" name="theme">
                    <option value="light" <?php if ($user['theme'] == 'light') echo 'selected'; ?>>Light</option>
                    <option value="dark" <?php if ($user['theme'] == 'dark') echo 'selected'; ?>>Dark</option>
                </select>
            </div>

            <!-- Email -->
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>

            <!-- Username -->
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required>
            </div>

            <!-- Password -->
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" value="<?php echo $user['user_password']; ?>" required>
            </div>

            <!-- Submit Button -->
            <button type="submit">Save Settings</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Crypto Express. All rights reserved.</p>
    </footer>
</body>
</html>

