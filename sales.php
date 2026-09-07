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
        form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        input, select, button {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #d7dfe9;
            font-size: 14px;
        }
        button {
            background: #2c7be5;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .message {
            margin: 10px 0 20px;
            padding: 10px;
            border-radius: 8px;
            background: #e9f7ef;
            color: #167a41;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
