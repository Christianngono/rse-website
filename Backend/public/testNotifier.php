<?php
require_once '../config/database.php';
require_once '../app/services/Notifier.php';

use App\Services\Notifier;

$notifier = new Notifier($pdo);
$userId = intval($_POST['user_id'] ?? 0);

if ($userId > 0) {
    $notifier->notifyUser($userId, "🔔 Test de notification", "Ceci est un test d’envoi via PHPMailer + Twilio.");
    echo json_encode(['success' => true, 'message' => 'Notification envoyée']);
} else {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
}