<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$profil = $_GET['profil'] ?? '';
$role = $_GET['role'] ?? '';

$sql = "SELECT id, username, role, profil, email, quiz_score, created_at FROM users WHERE 1=1";
$params = [];

if (php_sapi_name() === 'cli') {
    echo "Ce script ne doit pas être lancé en ligne de commande.\n";
    exit;
}

if ($profil) {
  $sql .= " AND profil = ?";
  $params[] = $profil;
}
if ($role) {
  $sql .= " AND role = ?";
  $params[] = $role;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

// Modifier le rôle d’un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role') {
    $userId = intval($_POST['user_id']);
    $newRole = $_POST['new_role'];

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);

    echo json_encode(['success' => true, 'message' => "Rôle mis à jour"]);
    exit;
}

// Supprimer un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $userId = intval($_POST['user_id']);

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    echo json_encode(['success' => true, 'message' => "Utilisateur supprimé"]);
    exit;
}

function logAdminAction($pdo, $adminUsername, $action, $targetUserId) {
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_username, action, target_user_id) VALUES (?, ?, ?)");
    $stmt->execute([$adminUsername, $action, $targetUserId]);
}