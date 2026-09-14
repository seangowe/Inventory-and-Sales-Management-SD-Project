<?php
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    $conn = new mysqli("localhost", "root", "", "sales_inventory");

    if ($conn->connect_error) {
        $message = "Database connection failed: " . $conn->connect_error;
    } else {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password']) && strtolower($user['role']) === strtolower($role)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if (strtolower($user['role']) === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: cashier.php");
                }
                exit;
            } else {
                $message = "Invalid username, password, or role.";
            }
        } else {
            $message = "Invalid username or password.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sales Inventory</title>
    <link rel="stylesheet" href="assets/styles.css">

</head>
<body class="login-page">
    <div class="login-box">
        <div class="brand-wrap">
            <img class="brand-logo" src="assets/stockpulse-logo.svg" alt="StockPulse logo">
            <div class="brand-name">StockPulse</div>
        </div>

        <h2>Login</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>

            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">Select role</option>
                <option value="admin">Admin</option>
                <option value="cashier">Cashier</option>
            </select>

            <button type="submit">Login</button>
        </form>

        <div class="footer-text">Welcome to StockPulse. A simple and efficient inventory management solution.</div>
    </div>
</body>
</html>
