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
    <link rel="stylesheet" href="assets/styles.css">

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
