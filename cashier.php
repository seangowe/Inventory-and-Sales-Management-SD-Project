<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'cashier') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="assets/styles.css">

</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            <img class="brand-logo" src="assets/stockpulse-logo.svg" alt="StockPulse logo">
            <span>StockPulse</span>
        </div>
        <nav class="nav">
            <a href="cashier.php">Dashboard</a>
            <a href="sales.php">Sales</a>
            <a href="products.php">Products</a>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="user-name">Cashier: <?php echo htmlspecialchars($_SESSION['username']); ?></div>
            <a class="logout" href="logout.php">Logout</a>
        </div>

        <div class="welcome">Welcome to the cashier panel.</div>

        <div class="dashboard-grid dashboard-grid-spaced">
            <div class="card">
                <h3>Process Sale</h3>
                <a href="sales.php">Open</a>
            </div>
            <div class="card">
                <h3>View Products</h3>
                <a href="products.php">Open</a>
            </div>
        </div>
    </main>
</body>
</html>
