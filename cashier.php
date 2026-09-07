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

        .nav a:hover {
            background: rgba(255,255,255,0.12);
        }

        .main {
            margin-left: 240px;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 18px 22px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }

        .user-name {
            font-weight: bold;
            color: #1f3a5f;
        }

        .logout {
            background: #2c7be5;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: bold;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-left: 5px solid #2c7be5;
        }

        .card h3 {
            margin: 0 0 10px;
            color: #1f3a5f;
        }

        .card a {
            text-decoration: none;
            color: #2c7be5;
            font-weight: bold;
        }

        .welcome {
            margin-top: 10px;
            font-size: 18px;
            color: #22354d;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand">InventoryPro</div>
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

        <div class="dashboard-grid" style="margin-top: 25px;">
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
