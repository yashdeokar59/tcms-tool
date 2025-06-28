<?php
<<<<<<< HEAD
require_once 'config.php';
=======
session_start();

// Database connection
require_once 'database.php';
try {
    $db = getDB();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed");
}
>>>>>>> 6fee269a179af23a34b06b68b49bbe716ca69c19

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
<<<<<<< HEAD
        $user = $stmt->fetch();
=======
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
>>>>>>> 6fee269a179af23a34b06b68b49bbe716ca69c19
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
<<<<<<< HEAD
            $_SESSION['user_role'] = $user['role'];
            
=======
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Update last login
>>>>>>> 6fee269a179af23a34b06b68b49bbe716ca69c19
            $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    } else {
        $error = 'Please fill all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TestFlow Pro - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<<<<<<< HEAD
=======
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
>>>>>>> 6fee269a179af23a34b06b68b49bbe716ca69c19
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
<<<<<<< HEAD
        .logo h2 {
            color: #667eea;
            font-weight: bold;
        }
=======
        .logo i {
            font-size: 3rem;
            color: #667eea;
        }
        .logo h2 {
            color: #333;
            margin-top: 1rem;
            font-weight: bold;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
>>>>>>> 6fee269a179af23a34b06b68b49bbe716ca69c19
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
<<<<<<< HEAD
            <h2>🧪 TestFlow Pro</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
=======
            <i class="fas fa-flask"></i>
            <h2>TestFlow Pro</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
>>>>>>> 6fee269a179af23a34b06b68b49bbe716ca69c19
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            
<<<<<<< HEAD
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        
        <div class="mt-4 text-center">
            <small class="text-muted">
                <strong>Demo Accounts:</strong><br>
                admin@testflow.com / password<br>
                manager@testflow.com / password<br>
                developer@testflow.com / password<br>
                tester@testflow.com / password
            </small>
        </div>
=======
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>
>>>>>>> 6fee269a179af23a34b06b68b49bbe716ca69c19
    </div>
</body>
</html>
