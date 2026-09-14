<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'sales_inventory');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$totalSales = $conn->query('SELECT COUNT(*) AS total FROM sales')->fetch_assoc()['total'];
$totalRevenue = $conn->query('SELECT COALESCE(SUM(total_price), 0) AS revenue FROM sales')->fetch_assoc()['revenue'];
$lowStockItems = $conn->query('SELECT * FROM products WHERE stock <= min_stock_level ORDER BY stock ASC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="assets/styles.css">

</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            <img class="brand-logo" src="assets/stockpulse-logo.svg" alt="StockPulse logo">
            <span>StockPulse</span>
        </div>
        <nav class="nav">
            <a href="admin.php">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="sales.php">Sales</a>
            <a href="users.php">Users</a>
            <a href="reports.php">Reports</a>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <h1>Reports</h1>
            <a class="logout" href="logout.php">Logout</a>
        </div>

        <div class="panel">
            <div class="stats">
                <div class="card">
                    Total Sales
                    <strong><?php echo htmlspecialchars($totalSales); ?></strong>
                </div>
                <div class="card">
                    Total Revenue
                    <strong><?php echo htmlspecialchars($totalRevenue); ?></strong>
                </div>
            </div>

            <h2>Low Stock Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Min Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($lowStockItems && $lowStockItems->num_rows > 0): ?>
                        <?php while ($item = $lowStockItems->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo htmlspecialchars($item['stock']); ?></td>
                                <td><?php echo htmlspecialchars($item['min_stock_level']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No low-stock products.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
