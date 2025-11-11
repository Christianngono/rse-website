<?php
declare(strict_types=1);
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

// Sécurité : accès uniquement via navigateur
if (php_sapi_name() === 'cli') {
    echo "Ce script ne doit pas être lancé en ligne de commande.\n";
    exit;
}

// Vérification session admin
if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

// === GET : Liste des utilisateurs ===
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $profil = $_GET['profil'] ?? '';
    $role   = $_GET['role'] ?? '';

    $sql = "SELECT id, username, role, profil, email, quiz_score, created_at, is_blocked, is_validated FROM users WHERE 1=1";
    $params = [];

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
    exit;
}

// === POST : Actions admin ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);

    $validRoles = ['admin', 'user'];
    $validProfils = ['novice', 'confirmé', 'expert'];

    switch ($action) {
        case 'change_role':
            $newRole = $_POST['new_role'] ?? '';
            if (!in_array($newRole, $validRoles)) {
                echo json_encode(['success' => false, 'message' => "Rôle invalide"]);
                break;
            }
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            logAdminAction($pdo, $_SESSION['user'],'change_role', $userId);
            notifyUser($pdo, $userId, "Changement de rôle", "Votre rôle a été mis à jour par l’administrateur.");
            echo json_encode(['success' => true, 'message' => "Rôle mis à jour"]);
            break;

        case 'delete_user':
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            logAdminAction($pdo, $_SESSION['user'], 'delete_user', $userId);
            notifyUser($pdo, $userId, "Suppression de compte", "Votre compte a été supprimé par l’administrateur.");
            echo json_encode(['success' => true, 'message' => "Utilisateur supprimé"]);
            break;

        case 'block_user':
            $stmt = $pdo->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
            $stmt->execute([$userId]);
            logAdminAction($pdo, $_SESSION['user'], 'block_user', $userId);
            notifyUser($pdo, $userId, "Compte bloqué", "Votre compte a été bloqué par l’administrateur.");
            echo json_encode(['success' => true, 'message' => "Utilisateur bloqué"]);
            break;

        case 'unblock_user':
            $stmt = $pdo->prepare("UPDATE users SET is_blocked = 0 WHERE id = ?");
            $stmt->execute([$userId]);
            logAdminAction($pdo, $_SESSION['user'], 'unblock_user', $userId);
            notifyUser($pdo, $userId, "Compte débloqué", "Votre compte a été débloqué par l’administrateur");
            echo json_encode(['success' => true, 'message' => "Utilisateur débloqué"]);
            break;

        case 'validate_user':
            $stmt = $pdo->prepare("UPDATE users SET is_validated = 1 WHERE id = ?");
            $stmt->execute([$userId]);
            logAdminAction($pdo, $_SESSION['user'], 'validate_user', $userId);
            notifyUser($pdo, $userId, "Inscription validée", "Votre inscription a été validée. Bienvenue !");
            echo json_encode(['success' => true, 'message' => "Inscription validée"]);
            break;

        case 'update_user':
            $fields = ['username', 'email', 'phone', 'profil'];
            $updates = [];
            $values = [];

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $updates[] = "$field = ?";
                    $values[] = trim($_POST[$field]);
                }
            }

            if ($updates) {
                $values[] = $userId;
                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);
                logAdminAction($pdo, $_SESSION['user'], 'update_user', $userId);
                notifyUser($pdo, $userId, "Profil mis à jour", "Vos informations ont été modifiées par l’administrateur.");
                echo json_encode(['success' => true, 'message' => "Informations mises à jour"]);
            } else {
                echo json_encode(['success' => false, 'message' => "Aucune donnée à mettre à jour"]);
            }
            break;

        case 'add_user':
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $profil   = $_POST['profil'] ?? 'débutant';
            $role     = $_POST['role'] ?? 'user';
            $password = $_POST['password'] ?? '';

            if (!$username || !$email || !$password) {
                echo json_encode(['success' => false, 'message' => "Champs obligatoires manquants"]);
                break;
            }

            if (!in_array($role, $validRoles) || !in_array($profil, $validProfils)) {
                echo json_encode(['success' => false, 'message' => "Rôle ou profil invalide"]);
                break;
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, profil, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $email, $phone, $profil, $role, $passwordHash]);
            $newId = intval($pdo->lastInsertId());
            logAdminAction($pdo, $_SESSION['user'], 'add_user', $newId);
            notifyUser($pdo, $newId, "Bienvenue sur la plateforme RSE", "Votre compte a été créé avec succès.");
            echo json_encode(['success' => true, 'message' => "Utilisateur ajouté"]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Action non reconnue"]);
            break;
    }

    exit;
}

// === Logger d’action admin ===
function logAdminAction($pdo, $adminUsername, $action, $targetUserId) {
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_username, action, target_user_id) VALUES (?, ?, ?)");
    $stmt->execute([$adminUsername, $action, $targetUserId]);
}

// === Notification email + SMS ===
function notifyUser($pdo, int $userId, string $subject, string $message): void {
    $stmt = $pdo->prepare("SELECT email, phone FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        notifyUserByEmail($pdo, $_SESSION['user'], $user['email'], $subject, $message);
        notifyUserBySMS($user['phone'], $message);
    }
}

function notifyUserByEmail($pdo, string $sender, string $emailTo, string $subject, string $message): void {
    $headers = "From: rse@quiz.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $sent = mail($emailTo, $subject, $message, $headers);

    if ($sent) {
        $stmt = $pdo->prepare("INSERT INTO email_logs (username, recipient, subject, sent_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$sender, $emailTo, $subject]);
    }
}

function notifyUserBySMS(string $phone, string $message): void {
    $smsGateway = $phone . '@sms.gateway.com'; // À adapter selon ton opérateur
    mail($smsGateway, '', $message);
}