<?php
// Start session to manage login state
session_start();

// Database connection settings
$servername = "localhost";
$username = "user";
$password = "Battle2511!";
$dbname = "crypto_express";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection and display errors only if in development environment
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['username'];
    $user_password = $_POST['password'];

    // Query to check if user exists
    $sql = "SELECT * FROM user_information WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $row = $result->fetch_assoc();
        
        // Verify the hashed password stored in the database with the entered password
        if (password_verify($user_password, $row['user_password'])) {
            // Valid user, start the session
            $_SESSION['user_id'] = $user_id;
            header("Location: dashboard.php");
            exit();
        } else {
            // Invalid password
            $error_message = "Invalid password. Please try again.";
        }
    } else {
        // Invalid user
        $error_message = "No user found with that username.";
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
    <title>Login - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        /* Dark Blue Banner */
        header {
            background-color: #2c3e50;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            color: #ecf0f1;
            font-size: 2rem;
            margin: 0;
        }

        /* Main Login Container */
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        /* Form input styling */
        .login-container input[type="text"], .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .login-container input[type="text"]:focus, .login-container input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
        }

        /* Submit button styling */
        .login-container input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .login-container input[type="submit"]:hover {
            background-color: #2980b9;
        }

        /* Error message styling */
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Forgot password link */
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: #3498db;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header with the title -->
    <header>
        <h1>Crypto Express</h1>
    </header>

    <!-- Main login container -->
    <div class="login-container">
        <h2>Login</h2>
        
        <!-- Error message if any -->
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Username input -->
            <input type="text" name="username" placeholder="Username" required><br>

            <!-- Password input -->
            <input type="password" name="password" placeholder="Password" required><br>

            <!-- Submit button -->
            <input type="submit" value="Login">
        </form>

        <!-- Forgot Password link -->
        <div class="forgot-password">
            <a href="forgot-password.php">Forgot your password?</a>
        </div>
    </div>
</body>
</html>


