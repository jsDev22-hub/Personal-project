<?php
// functions.php

function loginVisitor($pdo, $identifier, $reason) {
    // Kuhanin ang user base sa RFID, Email, o Google ID
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE rfid = :identifier 
        OR email = :identifier 
        OR google_id = :identifier
        LIMIT 1
    ");

    $stmt->execute([':identifier' => $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false; 
    }

    // Check kung blocked ang user
    if (isset($user['role']) && $user['role'] == 'Blocked') {
        return "blocked";
    }

    try {
        // I-log ang visit
        $stmt = $pdo->prepare("
            INSERT INTO visitor_logs (user_id, reason, visit_timestamp)
            VALUES (:user_id, :reason, NOW())
        ");

        $stmt->execute([
            ':user_id' => $user['id'],
            ':reason' => $reason
        ]);

        return $user; 
    } catch (PDOException $e) {
        return false;
    }
}

function searchLogs($pdo, $search) {
    $searchTerm = "%$search%";
    // Dinagdagan ng u.college para sa requirement
    $stmt = $pdo->prepare("
        SELECT 
            vl.reason,
            vl.visit_timestamp AS log_time,
            u.full_name AS name,
            u.program,
            u.college,
            u.user_type
        FROM visitor_logs vl
        JOIN users u ON vl.user_id = u.id
        WHERE u.full_name LIKE :search
           OR u.program LIKE :search
           OR u.college LIKE :search
           OR vl.reason LIKE :search
        ORDER BY vl.visit_timestamp DESC
    ");

    $stmt->execute([':search' => $searchTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStats($pdo, $type) {
    // Dynamic query para sa today, week, month
    if ($type == 'today') {
        $sql = "SELECT COUNT(*) as total FROM visitor_logs WHERE DATE(visit_timestamp) = CURDATE()";
    } elseif ($type == 'week') {
        $sql = "SELECT COUNT(*) as total FROM visitor_logs WHERE YEARWEEK(visit_timestamp, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($type == 'month') {
        $sql = "SELECT COUNT(*) as total FROM visitor_logs WHERE MONTH(visit_timestamp) = MONTH(CURDATE()) AND YEAR(visit_timestamp) = YEAR(CURDATE())";
    } else {
        return ['total' => 0];
    }

    $stmt = $pdo->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}