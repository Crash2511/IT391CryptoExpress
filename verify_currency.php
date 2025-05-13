<?php
session_start();

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['verification_code'];
    $user_id = $_SESSION['user_id'];
    $amount = $_SESSION['amount'];

    // Validate user session and verification code
    if (empty($user_id)) {
        $error_message = "You need to log in to verify your request.";
    } elseif ($entered_code != $_SESSION['verification_code']) {
        $error_message = "Invalid verification code. Please try again.";
    } else {
        // Update user's account balance
        $sql = "UPDATE user_information SET account_balance = account_balance + ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ds", $amount, $user_id);

        if ($stmt->execute()) {
            // Clear session data related to verification
            unset($_SESSION['verification_code']);
            unset($_SESSION['amount']);
            
            // Redirect to success page
            header("Location: add_currency_success.php");
            exit();
        } else {
            $error_message = "Failed to add currency. Please try again.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Currency - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Form container styling */
        form {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        /* Input styling */
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        /* Button styling */
        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #343a40;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #495057;
        }

        /* Form heading styling */
        h2 {
            text-align: center;
        }

        p {
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h1><a href="index.php">Crypto Express</a></h1>
    </header>

    <main>
        <h2>Verify Currency Addition</h2>
        <form id="verify-currency-form" method="POST" action="verify_currency.php">
            <label for="verification_code">Enter Verification Code:</label>
            <input type="text" id="verification_code" name="verification_code" required>

            <?php
            if (!empty($error_message)) {
                echo "<p style='color: red;'>$error_message</p>";
            }
            ?>

            <button type="submit">Verify and Add Currency</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>
</body>
</html>
