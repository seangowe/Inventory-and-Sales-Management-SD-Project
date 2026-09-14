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
                                        <form method="POST" action="users.php" onsubmit="return confirm('Delete this user?');" class="delete-form">
                                            <input type="hidden" name="delete_user_id" value="<?php echo (int)$user['id']; ?>">
                                            <button type="submit" class="danger-button">Delete</button>
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
