<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db_config.php';

$query = trim($_GET['q'] ?? '');
$limit = (int)($_GET['limit'] ?? 20);

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, stage_name 
        FROM members 
        WHERE stage_name LIKE ? 
        ORDER BY stage_name 
        LIMIT ?
    ");

    // 正確綁定：第一個參數是字串，第二個強制整數（不加引號）
    $stmt->bindValue(1, "%$query%", PDO::PARAM_STR);
    $stmt->bindValue(2, $limit,      PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = [];
    foreach ($results as $row) {
        $options[] = [
            'value' => (string)$row['id'],
            'text'  => htmlspecialchars($row['stage_name']) . ' (ID: ' . $row['id'] . ')'
        ];
    }

    echo json_encode($options);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

