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

        :root {
            --bg: #f3f7fb;
            --panel: #ffffff;
            --panel-soft: #f8fbff;
            --sidebar-dark: #102a43;
            --sidebar-light: #1f3a5f;
            --primary: #2c7be5;
            --primary-dark: #1f63c7;
            --text: #1f2d3d;
            --muted: #5e7187;
            --border: rgba(17, 35, 52, 0.08);
            --shadow: 0 12px 28px rgba(15, 34, 56, 0.08);
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, #edf4fb 0%, #f7f9fc 100%);
            color: var(--text);
        }

        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, var(--sidebar-dark), var(--sidebar-light));
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            padding: 24px 18px;
            box-shadow: 8px 0 24px rgba(16, 42, 67, 0.08);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #eaf4ff;
            padding: 8px 6px 14px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .brand-logo {
            width: 28px;
            height: 28px;
            display: block;
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 12px;
        }

        .nav a {
            color: #dfeeff;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.05);
            transform: translateX(2px);
        }

        .main {
            margin-left: 240px;
            padding: 30px;
        }

        .panel {
            background: var(--panel);
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 25px;
        }

        h1, h2 { color: var(--text); }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: linear-gradient(135deg, #f3f8ff, #eef5ff);
            border: 1px solid #d9e7ff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 22px rgba(44, 123, 229, 0.08);
        }
        .card strong {
            display: block;
            font-size: 26px;
            margin-top: 10px;
            color: var(--text);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #e6edf7;
            text-align: left;
        }
        th { background: #edf5ff; }
        tbody tr:hover {
            background: #f9fbff;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logout {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: bold;
            box-shadow: 0 10px 18px rgba(44, 123, 229, 0.18);
        }
    </style>
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
