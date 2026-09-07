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
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1f3a5f, #2c7be5);
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1f3a5f;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border: 1px solid #d9d9d9;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
        }

        input:focus, select:focus {
            border-color: #2c7be5;
            box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.15);
        }

        button {
            background: #2c7be5;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #1f63c7;
        }

        .message {
            margin-bottom: 18px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #ffe2e2;
            color: #a50000;
            font-size: 14px;
            text-align: center;
        }

        .footer-text {
            text-align: center;
            margin-top: 18px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Sales Inventory Login</h2>

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

        <div class="footer-text">Welcome to the Inventory and Sales Management System</div>
    </div>
</body>
</html>
