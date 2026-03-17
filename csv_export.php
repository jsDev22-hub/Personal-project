<?php
require_once 'config.php';
require_once 'functions.php';

$search = $_GET['search'] ?? '';
if ($search) {
    $logs = searchLogs($pdo, $search);
} else {
    $logs = $pdo->query("
        SELECT vl.*, u.name, u.program, u.user_type 
        FROM visitor_logs vl 
        JOIN users u ON vl.user_id = u.id 
        ORDER BY vl.log_time DESC
    ")->fetchAll();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="NEU_Library_Visitors_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Program', 'Type', 'Reason', 'Entry Time']);

foreach ($logs as $log) {
    fputcsv($output, [
        $log['name'],
        $log['program'],
        $log['user_type'],
        $log['reason'],
        $log['log_time']
    ]);
}

fclose($output);
exit;
?>