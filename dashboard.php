<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2d3d;
        }

        .container {
            max-width: 900px;
            margin: 80px auto;
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            color: #1f3a5f;
        }

        .welcome {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .logout {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #2c7be5;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard</h1>
        <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
        <div>Your role: <?php echo htmlspecialchars($_SESSION['role']); ?></div>

        <a class="logout" href="logout.php">Logout</a>
    </div>
</body>
</html>
