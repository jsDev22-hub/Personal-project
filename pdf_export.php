<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Kunin ang search query kung meron
$search = $_GET['search'] ?? '';

// Kunin ang data mula sa database
if (!empty($search)) {
    $logs = searchLogs($pdo, $search);
} else {
    $stmt = $pdo->query("SELECT vl.reason, vl.visit_timestamp as log_time, u.full_name as name, u.program FROM visitor_logs vl JOIN users u ON vl.user_id = u.id ORDER BY vl.visit_timestamp DESC");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>NEU Library - Export Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Itatago ang button kapag nag-save na as PDF */
        @media print {
            .no-print { display: none !important; }
            .container { width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold">NORTHEASTERN UNIVERSITY</h2>
            <h4>Library Visitor Log Report</h4>
            <p class="text-muted">Generated: <?= date('M d, Y h:i A') ?></p>
        </div>

        <table class="table table-bordered border-dark">
            <thead class="table-light">
                <tr>
                    <th>Visitor Name</th>
                    <th>Program</th>
                    <th>Reason of Visit</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['name']) ?></td>
                    <td><?= htmlspecialchars($log['program']) ?></td>
                    <td><?= htmlspecialchars($log['reason']) ?></td>
                    <td><?= date('Y-m-d | h:i A', strtotime($log['log_time'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-4 text-center no-print">
            <button onclick="window.print()" class="btn btn-primary px-5">
                <i class="fas fa-print"></i> Save as PDF / Print
            </button>
            <a href="admin.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>