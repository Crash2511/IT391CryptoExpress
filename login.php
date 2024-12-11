<?php
// Start session to manage login state
session_start();

// Database connection settings
$servername = "localhost";
$username = "user";
$password = ""; // your password here
$dbname = "crypto_express"; // Edit db if necessary here

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
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

        header a {
            text-decoration: none;
            color: #ecf0f1;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
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

        /* Button styling */
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

        .button-container {
            text-align: center;
            margin-top: 10px;
        }

        .button-container a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
            margin-top: 20px;
        }

        .button-container a:hover {
            background-color: #2980b9;
        }

        .error-message {
            color: red;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 20px;
        }

        .success-message {
            color: green;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        .form-footer a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>

<header>
    <a href="index.php">Crypto Express</a>
</header>

<div class="login-container">
    <h2>Login</h2>
    <!-- Error message display -->
    <?php if (isset($error_message)): ?>
        <div class="error-message"><?= $error_message; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>

    <div class="button-container">
        <a href="register.php">Register</a>
    </div>
    <div class="form-footer">
        <a href="forgot_password.php">Forgot Password?</a>
    </div>
</div>

</body>
</html>


