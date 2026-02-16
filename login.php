<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Demo credentials
$users = [
    'admin' => [
        'password' => 'admin123',
        'user_id' => 1,
        'name' => 'Admin User',
        'role' => 'Administrator'
    ],
    'user' => [
        'password' => 'user123',
        'user_id' => 2,
        'name' => 'Regular User',
        'role' => 'User'
    ]
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        // Set session variables
        $_SESSION['user_id'] = $users[$username]['user_id'];
        $_SESSION['username'] = $username;
        $_SESSION['name'] = $users[$username]['name'];
        $_SESSION['role'] = $users[$username]['role'];
        $_SESSION['login_time'] = date('Y-m-d H:i:s');

        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c33;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .demo-credentials {
            margin-top: 30px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .demo-credentials h3 {
            color: #333;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }

        .credential-box {
            background: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 13px;
        }

        .credential-box:last-child {
            margin-bottom: 0;
        }

        .credential-box strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Login</h1>
            <p>Enter your credentials to access the dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="demo-credentials">
            <h3>📋 Demo Credentials</h3>
            <div class="credential-box">
                <strong>Admin:</strong> admin / admin123
            </div>
            <div class="credential-box">
                <strong>User:</strong> user / user123
            </div>
        </div>
    </div>
</body>
</html>