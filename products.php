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
$canManage = strtolower($_SESSION['role']) === 'admin';
$editingProduct = null;
$search = trim($_GET['search'] ?? '');

if (isset($_GET['delete_id']) && $canManage) {
    $delete_id = (int) $_GET['delete_id'];

    $deleteStmt = $conn->prepare('DELETE FROM products WHERE id = ?');
    $deleteStmt->bind_param('i', $delete_id);

    if ($deleteStmt->execute()) {
        $message = 'Product deleted successfully.';
    } else {
        $message = 'Error deleting product: ' . $deleteStmt->error;
    }

    $deleteStmt->close();
}

if (isset($_GET['edit_id']) && $canManage) {
    $editId = (int) $_GET['edit_id'];
    $editStmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $editStmt->bind_param('i', $editId);
    $editStmt->execute();
    $editResult = $editStmt->get_result();

    if ($editResult && $editResult->num_rows > 0) {
        $editingProduct = $editResult->fetch_assoc();
    }

    $editStmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManage) {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'update_stock') {
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $new_stock = (int) ($_POST['stock'] ?? 0);

        if ($product_id > 0) {
            $stmt = $conn->prepare('UPDATE products SET stock = ? WHERE id = ?');
            $stmt->bind_param('ii', $new_stock, $product_id);

            if ($stmt->execute()) {
                $message = 'Stock updated successfully.';
            } else {
                $message = 'Error updating stock: ' . $stmt->error;
            }

            $stmt->close();
        }
    } else {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $buying_price = $_POST['buying_price'] ?? 0;
        $selling_price = $_POST['selling_price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $min_stock_level = $_POST['min_stock_level'] ?? 5;

        if ($name !== '' && $category !== '') {
            if ($action === 'update') {
                $id = (int) ($_POST['product_id'] ?? 0);
                $stmt = $conn->prepare('UPDATE products SET name = ?, category = ?, buying_price = ?, selling_price = ?, stock = ?, min_stock_level = ? WHERE id = ?');
                $stmt->bind_param('ssddiii', $name, $category, $buying_price, $selling_price, $stock, $min_stock_level, $id);

                if ($stmt->execute()) {
                    $message = 'Product updated successfully.';
                } else {
                    $message = 'Error updating product: ' . $stmt->error;
                }

                $stmt->close();
            } else {
                $stmt = $conn->prepare('INSERT INTO products (name, category, buying_price, selling_price, stock, min_stock_level) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssddii', $name, $category, $buying_price, $selling_price, $stock, $min_stock_level);

                if ($stmt->execute()) {
                    $message = 'Product added successfully.';
                } else {
                    $message = 'Error adding product: ' . $stmt->error;
                }

                $stmt->close();
            }
        } else {
            $message = 'Please fill in all required product fields.';
        }
    }
}

if ($search !== '') {
    $searchTerm = "%$search%";
    $stmt = $conn->prepare('SELECT * FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY id DESC');
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query('SELECT * FROM products ORDER BY id DESC');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
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
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .search-box {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .search-box input {
            min-width: 220px;
        }
        .action-links {
            display: flex;
            gap: 10px;
        }
        .edit-link, .delete-link {
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
        }
        .edit-link {
            background: #eaf3ff;
            color: #1f3a5f;
        }
        .delete-link {
            background: #ffe5e5;
            color: #b42318;
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
            <h1>Products</h1>
            <a class="logout" href="logout.php">Logout</a>
        </div>

        <div class="panel">
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($canManage): ?>
                <h2><?php echo $editingProduct ? 'Edit Product' : 'Add New Product'; ?></h2>
                <form method="POST" action="products.php">
                    <?php if ($editingProduct): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($editingProduct['id']); ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="add">
                    <?php endif; ?>

                    <input type="text" name="name" value="<?php echo htmlspecialchars($editingProduct['name'] ?? ''); ?>" placeholder="Product name" required>
                    <input type="text" name="category" value="<?php echo htmlspecialchars($editingProduct['category'] ?? ''); ?>" placeholder="Category" required>
                    <input type="number" step="0.01" name="buying_price" value="<?php echo htmlspecialchars($editingProduct['buying_price'] ?? ''); ?>" placeholder="Buying price" required>
                    <input type="number" step="0.01" name="selling_price" value="<?php echo htmlspecialchars($editingProduct['selling_price'] ?? ''); ?>" placeholder="Selling price" required>
                    <input type="number" name="stock" value="<?php echo htmlspecialchars($editingProduct['stock'] ?? ''); ?>" placeholder="Stock" required>
                    <input type="number" name="min_stock_level" value="<?php echo htmlspecialchars($editingProduct['min_stock_level'] ?? 5); ?>" placeholder="Min stock level" required>
                    <button type="submit"><?php echo $editingProduct ? 'Update Product' : 'Add Product'; ?></button>
                </form>
            <?php endif; ?>

            <div class="toolbar">
                <form method="GET" action="products.php" class="search-box">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or category">
                    <button type="submit">Search</button>
                    <?php if ($search !== ''): ?>
                        <a href="products.php" style="text-decoration:none; color:#2c7be5; font-weight:bold;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Buying Price</th>
                        <th>Selling Price</th>
                        <th>Stock</th>
                        <th>Min Stock</th>
                        <?php if ($canManage): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($product = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td><?php echo htmlspecialchars($product['buying_price']); ?></td>
                                <td><?php echo htmlspecialchars($product['selling_price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                <td><?php echo htmlspecialchars($product['min_stock_level']); ?></td>
                                <?php if ($canManage): ?>
                                    <td>
                                        <div class="action-links">
                                            <a class="edit-link" href="products.php?edit_id=<?php echo $product['id']; ?>">Edit</a>
                                            <a class="delete-link" href="products.php?delete_id=<?php echo $product['id']; ?>" onclick="return confirm('Delete this product?');">Delete</a>
                                            <form method="POST" action="products.php" style="display:inline;">
                                                <input type="hidden" name="action" value="update_stock">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" style="width:70px; padding:5px; border-radius:6px; border:1px solid #d7dfe9;" min="0">
                                                <button type="submit" style="padding:6px 8px; font-size:12px;">Update Stock</button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $canManage ? '8' : '7'; ?>">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
