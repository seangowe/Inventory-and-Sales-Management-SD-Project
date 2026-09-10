<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--panel);
            padding: 18px 22px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            border: 1px solid var(--border);
        }

        .user-name {
            font-weight: 700;
            color: var(--text);
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

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--panel);
            border-radius: 14px;
            padding: 25px 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            border-left: 5px solid var(--primary);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(15, 34, 56, 0.12);
        }

        .card h3 {
            margin: 0 0 10px;
            color: var(--text);
        }

        .card a {
            text-decoration: none;
            color: var(--primary);
            font-weight: bold;
        }

        .welcome {
            margin-top: 8px;
            font-size: 18px;
            color: var(--muted);
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
            <div class="user-name">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></div>
            <a class="logout" href="logout.php">Logout</a>
        </div>

        <div class="welcome">Welcome to the admin panel.</div>

        <div class="dashboard-grid" style="margin-top: 25px;">
            <div class="card">
                <h3>Manage Products</h3>
                <a href="products.php">Open</a>
            </div>
            <div class="card">
                <h3>Sales Records</h3>
                <a href="sales.php">Open</a>
            </div>
            <div class="card">
                <h3>Users</h3>
                <a href="users.php">Open</a>
            </div>
            <div class="card">
                <h3>Reports</h3>
                <a href="reports.php">Open</a>
            </div>
        </div>
    </main>
</body>
</html>
