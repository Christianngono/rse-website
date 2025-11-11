<?php
declare(strict_types=1);
require_once '../config/database.php';
header('Content-Type: application/json');

// === Paramètres sécurisés ===
$date = $_GET['date'] ?? '';
$admin = $_GET['admin'] ?? '';
$action = $_GET['action'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$sort = $_GET['sort'] ?? 'timestamp';
$order = strtoupper($_GET['order'] ?? 'DESC');
$all = isset($_GET['all']) && $_GET['all'] === 'true';


// === Validation des champs de tri ===
$validSorts = ['timestamp', 'admin_username', 'action', 'target_user_id'];
if (!in_array($sort, $validSorts)) $sort = 'timestamp';
if (!in_array($order, ['ASC', 'DESC'])) $order = 'DESC';

// === Construction de la requête ===
$sql = "SELECT admin_username, action, target_user_id, timestamp FROM admin_logs WHERE 1=1";
$params = [];

if ($date) {
    $sql .= " AND DATE(timestamp) = ?";
    $params[] = $date;
}
if ($admin) {
    $sql .= " AND admin_username LIKE ?";
    $params[] = "%$admin%";
}
if ($action) {
    $sql .= " AND action LIKE ?";
    $params[] = "%$action%";
}

// === Pagination ou export complet ===
if (!$all) {
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $sql .= " ORDER BY $sort $order LIMIT $limit OFFSET $offset";
} else {
    $sql .= " ORDER BY $sort $order";
}

// === Exécution de la requête ===
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['logs' => $logs]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => false,
        'erreur' => 'Erreur lors de la récupération des logs', 
        'details' => $e->getMessage()
    ]);
}
