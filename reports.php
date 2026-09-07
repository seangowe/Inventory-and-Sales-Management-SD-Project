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
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #edf3f9;
            color: #1f2d3d;
        }

        .sidebar {
            width: 240px;
            background: #1f3a5f;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            padding: 25px 20px;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #dfeeff;
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .nav a {
            color: #dfeeff;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
        }

        .main {
            margin-left: 240px;
            padding: 30px;
        }

        .panel {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            padding: 25px;
        }

        h1, h2 { color: #1f3a5f; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: #eef5ff;
            border: 1px solid #d7e6ff;
            border-radius: 10px;
            padding: 20px;
        }
        .card strong {
            display: block;
            font-size: 24px;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5eaf2;
            text-align: left;
        }
        th { background: #edf4ff; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logout {
            background: #2c7be5;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand">InventoryPro</div>
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
