<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'sales_inventory');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$message = '';
$canCreateSale = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;

    if ($product_id > 0 && $quantity > 0) {
        $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();

        if ($product) {
            if ($product['stock'] < $quantity) {
                $message = 'Not enough stock available for this sale.';
            } else {
                $unit_price = $product['selling_price'];
                $total_price = $unit_price * $quantity;
                $user_id = $_SESSION['user_id'];

                $stmt = $conn->prepare('INSERT INTO sales (product_id, quantity, total_price, user_id) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('iidd', $product_id, $quantity, $total_price, $user_id);

                if ($stmt->execute()) {
                    $newStock = $product['stock'] - $quantity;
                    $update = $conn->prepare('UPDATE products SET stock = ? WHERE id = ?');
                    $update->bind_param('ii', $newStock, $product_id);
                    $update->execute();
                    $update->close();

                    $message = 'Sale recorded successfully.';
                } else {
                    $message = 'Error storing sale: ' . $stmt->error;
                }

                $stmt->close();
            }
        } else {
            $message = 'Selected product does not exist.';
        }
    } else {
        $message = 'Please select a product and quantity.';
    }
}

$products = $conn->query('SELECT * FROM products ORDER BY name ASC');
$sales = $conn->query('SELECT s.*, p.name AS product_name, u.username FROM sales s JOIN products p ON p.id = s.product_id JOIN users u ON u.id = s.user_id ORDER BY s.sale_date DESC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
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
            --success: #1e8f5d;
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
        form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        input, select, button {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 14px;
            background: #fbfdff;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.12);
        }
        button {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 10px 18px rgba(44, 123, 229, 0.18);
        }
        .message {
            margin: 10px 0 20px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #e9f7ef;
            color: var(--success);
            border: 1px solid rgba(30, 143, 93, 0.08);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
            <a href="<?php echo strtolower($_SESSION['role']) === 'admin' ? 'admin.php' : 'cashier.php'; ?>">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="sales.php">Sales</a>
            <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
                <a href="users.php">Users</a>
                <a href="reports.php">Reports</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <h1>Sales</h1>
            <a class="logout" href="logout.php">Logout</a>
        </div>

        <div class="panel">
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <h2>New Sale</h2>
            <form method="POST" action="sales.php">
                <select name="product_id" required>
                    <option value="">Select product</option>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?> (Stock: <?php echo $product['stock']; ?>)</option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="quantity" min="1" placeholder="Quantity" required>
                <button type="submit">Record Sale</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Sold By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sales && $sales->num_rows > 0): ?>
                        <?php while ($sale = $sales->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['id']); ?></td>
                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($sale['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($sale['total_price']); ?></td>
                                <td><?php echo htmlspecialchars($sale['username']); ?></td>
                                <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No sales found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
