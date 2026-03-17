<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$error = '';
$visitor_data = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']);
    $reason = $_POST['reason'] ?? '';
    
    $result = loginVisitor($pdo, $identifier, $reason);

    if ($result === "blocked") {
        $error = "ACCESS DENIED: Your account is blocked.";
    } elseif ($result) {
        $visitor_data = $result;
        
        // RBAC: Kung ang nag-login ay si Prof or Admin, i-save sa session
        if ($result['email'] == 'jcesperanza@neu.edu.ph' || $result['role'] == 'Admin') {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['role'] = 'Admin';
            $_SESSION['name'] = $result['full_name'];
        }

        header("refresh:5;url=index.php");
    } else {
        $error = "Invalid Identification. Please register first.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEU Library Visitor Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root { --neu-blue: #1a73e8; --neu-dark-blue: #0d47a1; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .hero-section { min-height: 90vh; display: flex; align-items: center; justify-content: center; }
        .welcome-card { background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%); color: white; border-radius: 25px; }
        .login-card { border-radius: 20px; border: none; }
        .btn-primary { background: var(--neu-blue); border: none; font-weight: 600; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-university me-2"></i> NEU LIBRARY LOG</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Admin'): ?>
                <a href="admin.php" class="btn btn-warning btn-sm fw-bold">GO TO ADMIN DASHBOARD</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="hero-section">
            
            <?php if ($visitor_data): ?>
                <div class="col-md-7 animate__animated animate__zoomIn">
                    <div class="card welcome-card shadow-lg text-center p-5">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-5x text-white mb-4"></i>
                            <h3 class="display-6">Mabuhay!</h3>
                            <h1 class="fw-bold text-uppercase mb-2"><?php echo htmlspecialchars($visitor_data['full_name']); ?></h1>
                            <p class="fs-5 opacity-75"><?php echo htmlspecialchars($visitor_data['program']); ?> (<?php echo htmlspecialchars($visitor_data['college']); ?>)</p>
                            
                            <div class="bg-white text-primary rounded-pill py-2 px-5 d-inline-block fw-bold my-4 shadow">
                                Welcome to NEU Library!
                            </div>
                            
                            <br>
                            <a href="index.php" class="btn btn-light rounded-pill px-4 fw-bold">Done / Next Visitor</a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="col-md-5 animate__animated animate__fadeIn">
                    <div class="card login-card shadow-lg">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h2 class="fw-bold">Visitor Login</h2>
                                <p class="text-muted">Tap RFID or Enter Institutional Email</p>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-danger border-0 animate__animated animate__shakeX">
                                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Identification</label>
                                    <input type="text" name="identifier" class="form-control form-control-lg" placeholder="Email or RFID" required autofocus>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Purpose of Visit</label>
                                    <select name="reason" class="form-select form-select-lg" required>
                                        <option value="" disabled selected>Choose reason...</option>
                                        <option value="Reading">Reading</option>
                                        <option value="Researching">Researching</option>
                                        <option value="Meeting">Meeting</option>
                                        <option value="Group Study">Group Study</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">SIGN IN</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>