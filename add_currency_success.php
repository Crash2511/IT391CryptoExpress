<?php
session_start();

// Redirect to index page after a few seconds
header("refresh:5;url=index.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Addition Successful - Crypto Express</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Center content styling */
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        h2 {
            color: #28a745;
        }

        p {
            margin-top: 20px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1><a href="index.php">Crypto Express</a></h1>
    </header>

    <main>
        <div class="success-container">
            <h2>Currency Addition Successful!</h2>
            <p>Your requested amount has been successfully added to your account.</p>
            <p>You will be redirected to the homepage shortly. If not, <a href="index.php">click here</a>.</p>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Crypto Express</p>
    </footer>
</body>
</html>
