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

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle deletion when `delete_user_id` is provided
    if (isset($_POST['delete_user_id'])) {
        $deleteId = (int) $_POST['delete_user_id'];

        // Only allow admin with ID 1 to delete users
        if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] !== 1 || strtolower($_SESSION['role']) !== 'admin') {
            $message = 'You are not authorized to delete users.';
        } elseif ($deleteId === 1) {
            $message = 'Cannot delete the primary admin.';
        } else {
            $delStmt = $conn->prepare('DELETE FROM users WHERE id = ?');
            $delStmt->bind_param('i', $deleteId);
            if ($delStmt->execute()) {
                if ($delStmt->affected_rows > 0) {
                    $message = 'User deleted successfully.';
                } else {
                    $message = 'User not found or already deleted.';
                }
            } else {
                $message = 'Error deleting user: ' . $delStmt->error;
            }
            $delStmt->close();
        }

    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'cashier';

        if ($username !== '' && $password !== '') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role)');
            $stmt->bind_param('sss', $username, $hashed, $role);

            if ($stmt->execute()) {
                $message = 'User saved successfully.';
            } else {
                $message = 'Error saving user: ' . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = 'Please enter a username and password.';
        }
    }
}

$users = $conn->query('SELECT id, username, role FROM users ORDER BY id ASC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
            padding: 10px;
            border-radius: 8px;
            background: #e9f7ef;
            color: #167a41;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
            <h1>Manage Users</h1>
            <a class="logout" href="logout.php">Logout</a>
        </div>

        <div class="panel">
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <h2>Add User</h2>
            <form method="POST" action="users.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role">
                    <option value="admin">Admin</option>
                    <option value="cashier" selected>Cashier</option>
                </select>
                <button type="submit">Save User</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1 && strtolower($_SESSION['role']) === 'admin' && (int)$user['id'] !== 1): ?>
                                        <form method="POST" action="users.php" onsubmit="return confirm('Delete this user?');" style="display:inline">
                                            <input type="hidden" name="delete_user_id" value="<?php echo (int)$user['id']; ?>">
                                            <button type="submit" style="background:#e05353;color:#fff;padding:8px 10px;border-radius:6px;border:none;cursor:pointer;">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
