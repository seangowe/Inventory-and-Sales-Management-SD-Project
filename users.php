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
            padding: 10px 12px;
            border-radius: 10px;
            background: #e9f7ef;
            color: var(--success);
            margin-bottom: 20px;
            border: 1px solid rgba(30, 143, 93, 0.08);
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
