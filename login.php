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

        :root {
            --bg-dark: #0f2138;
            --bg-mid: #1d3c67;
            --primary: #2c7be5;
            --primary-dark: #1f63c7;
            --panel: #ffffff;
            --panel-soft: #f4f8ff;
            --text: #1f2d3d;
            --muted: #62748a;
            --border: rgba(18, 37, 53, 0.08);
            --shadow: 0 18px 40px rgba(14, 32, 54, 0.18);
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, rgba(255,255,255,0.18), transparent 30%), linear-gradient(135deg, var(--bg-dark), var(--bg-mid));
        }

        .login-box {
            width: 100%;
            max-width: 430px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(6px);
            padding: 32px 30px 28px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .brand-logo {
            width: 54px;
            height: 54px;
            display: block;
            filter: drop-shadow(0 8px 18px rgba(44, 123, 229, 0.25));
        }

        .brand-name {
            font-size: 30px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: 0.2px;
        }

        h2 {
            text-align: center;
            margin: 0 0 22px;
            color: var(--text);
            font-size: 28px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            color: var(--text);
            font-weight: 700;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            background: #f9fbff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(44, 123, 229, 0.12);
            background: #fff;
        }

        button {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            border: none;
            padding: 13px 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 18px rgba(44, 123, 229, 0.22);
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 20px rgba(44, 123, 229, 0.28);
        }

        .message {
            margin-bottom: 18px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #ffe9e9;
            color: #9c1e1e;
            font-size: 14px;
            text-align: center;
            border: 1px solid rgba(156, 30, 30, 0.08);
        }

        .footer-text {
            text-align: center;
            margin-top: 18px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
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
