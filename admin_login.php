<?php
session_start();
require_once 'config.php';

// Logout Logic
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// Redirect kung naka-login na
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Credentials check
    if ($username == 'admin' && $password == 'neu123') {
        // IMPORTANT: Dapat magtugma ito sa security check ng admin.php
        $_SESSION['user_id'] = 0; // Admin ID placeholder
        $_SESSION['role'] = 'Admin'; 
        $_SESSION['admin_logged_in'] = true; 
        
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid credentials. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NEU Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card { 
            max-width: 400px; 
            width: 100%; 
            margin: auto; 
        }
        .card { 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            overflow: hidden;
        }
        .card-header-icon {
            background: #f8f9fa;
            padding: 30px 0;
            border-bottom: 1px solid #eee;
        }
        .btn-primary { 
            background-color: #1a73e8; 
            border: none; 
            padding: 12px; 
            font-weight: bold; 
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #1557b0;
            transform: translateY(-2px);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4 login-card">
                <div class="card border-0">
                    <div class="card-header-icon text-center">
                        <i class="fas fa-shield-halved fa-4x text-primary mb-3"></i>
                        <h3 class="fw-bold mb-0">Admin Portal</h3>
                        <p class="text-muted small">NEU Library System</p>
                    </div>
                    <div class="card-body p-4">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger text-center py-2 small">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-uppercase">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-uppercase">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">
                                SIGN IN <i class="fas fa-arrow-right-to-bracket ms-2"></i>
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="index.php" class="text-decoration-none small text-secondary hover-primary">
                                <i class="fas fa-house me-1"></i> Back to Public Entry
                            </a>
                        </div>
                    </div>
                </div>
                <p class="text-center text-white-50 mt-4 small">&copy; 2026 NEU Library System</p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>