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
    <link rel="stylesheet" href="assets/styles.css">

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
