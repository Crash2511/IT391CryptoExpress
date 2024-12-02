<?php
// Start session to manage registration state
session_start();

// Database connection settings
$servername = "localhost";
$username = "user";
$password = "Battle2511!";
$dbname = "crypto_express";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['username'];
    $user_password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $email = $_POST['email'];

    // Email details for sending confirmation
    $sender_email = "cryptoExpress@gmail.com";
    $sender_password = "your_gmail_app_password";
    $smtp_host = "smtp.gmail.com";
    $smtp_port = 587;

    // Check if passwords match
    if ($user_password != $confirm_password) {
        $error_message = "Passwords do not match. Please try again.";
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);

        // Check if user already exists
        $sql = "SELECT * FROM user_information WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose another.";
        } else {
            // Insert new user into the database
            $sql = "INSERT INTO user_information (user_id, user_password, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $user_id, $hashed_password, $email);

            if ($stmt->execute()) {
                // Send a confirmation email using PHPMailer
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
                $mail->addAddress($email);
                $mail->Subject = "Welcome to Crypto Express!";
                $mail->Body = "Hello $user_id,\n\nThank you for registering at Crypto Express. We are excited to have you on board!\n\n- The Crypto Express Team";

                if (!$mail->send()) {
                    $error_message = "Registration successful, but failed to send confirmation email.";
                }

                // Registration successful, redirect to confirmation page regardless of email status
                header("Location: registration_success.php");
                exit();
            } else {
                $error_message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2c3e50;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            color: #ecf0f1;
            font-size: 2rem;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
        }

        input[type="submit"]:hover {
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
        <h1>Crypto Express</h1>
    </header>

    <div class="container">
        <h2>Create an Account</h2>

        <!-- Error message -->
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm-password" required>

            <input type="submit" value="Register">
        </form>

        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

</body>
</html>







