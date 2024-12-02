<?php
session_start();

// Database connection settings
$servername = "localhost";
$username = "user";
$password = "Battle2511!";
$dbname = "crypto_express";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id'];

    // Email details for sending verification
    $sender_email = "cryptoExpress@gmail.com";
    $sender_password = "your_gmail_app_password";
    $smtp_host = "smtp.gmail.com";
    $smtp_port = 587;

    // Validate user session
    if (empty($user_id)) {
        $error_message = "You need to log in to add currency.";
    } else {
        // Validate amount to ensure it is positive
        if ($amount <= 0) {
            $error_message = "Amount must be greater than zero.";
        } else {
            // Generate a verification code
            $verification_code = rand(100000, 999999);

            // Fetch user email from the database
            $sql = "SELECT email FROM user_information WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_email = $row['email'];

                // Send verification email using PHPMailer
                require 'PHPMailer/PHPMailerAutoload.php';
                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->Host = $smtp_host;
                $mail->SMTPAuth = true;
                $mail->Username = $sender_email;
                $mail->Password = $sender_password;
                $mail->SMTPSecure = 'tls';
                $mail->Port = $smtp_port;

                $mail->setFrom($sender_email, 'Crypto Express');
                $mail->addAddress($user_email);
                $mail->Subject = "Your Crypto Express Verification Code";
                $mail->Body = "Please confirm your request to add funds to your account. Your verification code is: $verification_code";

                if ($mail->send()) {
                    $_SESSION['verification_code'] = $verification_code;
                    $_SESSION['amount'] = $amount;
                    header("Location: verify_currency.php");
                    exit();
                } else {
                    $error_message = "Failed to send verification email. Please try again.";
                }
            } else {
                $error_message = "User not found.";
            }

            $stmt->close();
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
    <title>Add Currency - Crypto Express</title>
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
        input[type="number"] {
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
        <h2>Add Currency (in USD)</h2>
        <form id="add-currency-form" method="POST" action="add_currency.php">
            <label for="amount">Amount to Add ($):</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>

            <?php
            if (!empty($error_message)) {
                echo "<p style='color: red;'>$error_message</p>";
            }
            ?>

            <button type="submit">Add Currency</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>
</body>
</html>


