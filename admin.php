<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

/** * SECURITY CHECK 
 * Tinitiyak na ang naka-login ay Admin. 
 * Kung hindi, babalik siya sa login page.
 */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: admin_login.php");
    exit;
}

$msg = '';

/** * BLOCK USER LOGIC 
 * Kapag pinindot ang block button, ia-update ang 'is_blocked' column sa database.
 */
if (isset($_POST['action']) && $_POST['action'] == 'block') {
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
    if($stmt->execute([$user_id])) {
        $msg = "User successfully blocked!";
    }
}

/** * DATA RETRIEVAL & SEARCH 
 * Kumukuha ng logs base sa search input o default list (last 100).
 */
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $logs = searchLogs($pdo, $search);
} else {
    $stmt = $pdo->query("
        SELECT vl.user_id, vl.reason, vl.visit_timestamp AS log_time, 
               u.full_name AS name, u.program, u.user_type
        FROM visitor_logs vl
        JOIN users u ON vl.user_id = u.id
        ORDER BY vl.visit_timestamp DESC LIMIT 100
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** * STATS CARDS 
 * Kinukuha ang data para sa Dashboard Cards.
 */
$stats = [
    'today' => getStats($pdo, 'today'),
    'week'  => getStats($pdo, 'week'),
    'month' => getStats($pdo, 'month')
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEU Library | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .stat-card { border: none; border-left: 5px solid #0d6efd; transition: 0.3s; border-radius: 12px; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .navbar { background: #1a237e !important; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .badge-program { background-color: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb; }
        
        /* CSS for PDF/Print functionality */
        @media print {
            .no-print, .btn, .form-control, .navbar, .alert { display: none !important; }
            body { background: white; }
            .table-container { box-shadow: none; border: none; padding: 0; }
            .container { max-width: 100%; width: 100%; margin: 0; }
            .stat-card { border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow mb-4 no-print">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="fas fa-university me-2"></i> NEU LIBRARY ADMIN</a>
        <div class="ms-auto">
            <span class="text-white me-3 small d-none d-md-inline">Welcome, Admin</span>
            <a href="index.php" class="btn btn-outline-light btn-sm me-2"><i class="fas fa-sign-in-alt"></i> Visitor Entry</a>
            <a href="logout.php" class="btn btn-danger btn-sm" title="Logout"><i class="fas fa-power-off"></i></a>
        </div>
    </div>
</nav>

<div class="container mb-5">
    
    <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card stat-card shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase small">Today's Visitors</h6>
                        <h2 class="fw-bold mb-0"><?= $stats['today']['total'] ?? 0 ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-users fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card stat-card shadow-sm p-3" style="border-left-color: #198754;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase small">This Week</h6>
                        <h2 class="fw-bold mb-0"><?= $stats['week']['total'] ?? 0 ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-calendar-week fa-lg text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm p-3" style="border-left-color: #ffc107;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase small">This Month</h6>
                        <h2 class="fw-bold mb-0"><?= $stats['month']['total'] ?? 0 ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-chart-line fa-lg text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3 no-print align-items-center">
        <div class="col-md-8 mb-3 mb-md-0">
            <form class="d-flex gap-2" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, program, or reason..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                </div>
                <?php if(!empty($search)): ?>
                    <a href="admin.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="col-md-4 text-md-end">
            <button onclick="window.print()" class="btn btn-dark shadow-sm">
                <i class="fas fa-file-pdf me-2"></i> Export PDF / Print
            </button>
        </div>
    </div>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0"><i class="fas fa-list-ul me-2 text-primary"></i> Library Visit Logs</h5>
            <span class="badge bg-light text-dark border p-2 no-print">Showing <?= count($logs) ?> records</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Visitor Name</th>
                        <th class="py-3">Program / Position</th>
                        <th class="py-3">Reason</th>
                        <th class="py-3">Date & Time</th>
                        <th class="py-3 no-print text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($logs)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i><br>No records found.
                        </td></tr>
                    <?php endif; ?>
                    
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($log['name']) ?></div>
                            <div class="text-muted x-small" style="font-size: 0.75rem;"><?= htmlspecialchars($log['user_type']) ?></div>
                        </td>
                        <td><span class="badge badge-program rounded-pill px-3"><?= htmlspecialchars($log['program'] ?: 'N/A') ?></span></td>
                        <td><?= htmlspecialchars($log['reason']) ?></td>
                        <td>
                            <div class="text-dark small"><?= date("M d, Y", strtotime($log['log_time'])) ?></div>
                            <div class="text-muted x-small" style="font-size: 0.75rem;"><i class="far fa-clock me-1"></i><?= date("h:i A", strtotime($log['log_time'])) ?></div>
                        </td>
                        <td class="no-print text-center">
                            <form method="POST" onsubmit="return confirm('Warning: Sigurado ka bang i-block ang user na ito? Hindi na siya makakapag-login sa Library.')">
                                <input type="hidden" name="user_id" value="<?= $log['user_id'] ?>">
                                <input type="hidden" name="action" value="block">
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Block User">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>