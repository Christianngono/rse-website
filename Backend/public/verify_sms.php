<?php
require '../../config/database.php';
require_once '../../vendor/autoload.php';

use Twilio\Rest\Client;

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

$code = $_POST['code'] ?? '';
$phone = $_POST['phone'] ?? '';

if (!$code || !$phone) {
    $response['message'] = "Code et numéro requis.";
    echo json_encode($response);
    exit;
}

$config = require '../../config/config.php';
$sid = $config['twilio']['sid'];
$token = $config['twilio']['token'];
$verifySid = $config['twilio']['verify_sid'];

$client = new Client($sid, $token);

try {
    $verificationCheck = $client->verify->v2->services($verifySid)
        ->verificationChecks
        ->create(['to' => $phone, 'code' => $code]);

    // Récupérer l'utilisateur lié au numéro
    $stmt = $pdo->prepare("SELECT username FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        $response['message'] = "Numéro inconnu.";
        echo json_encode($response);
        exit;
    }

    $username = $user['username'];

    if ($verificationCheck->status === "approved") {
        // Activer le compte
        $stmt = $pdo->prepare("UPDATE users SET is_active = TRUE WHERE username = ?");
        $stmt->execute([$username]);

        // Journaliser la vérification
        $stmt = $pdo->prepare("INSERT INTO phone_verifications (username, phone, code, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $phone, $code, 'validé']);

        $response['success'] = true;
        $response['message'] = "✅ Numéro vérifié et compte activé !";
    } else {
        // Journaliser l’échec
        $stmt = $pdo->prepare("INSERT INTO phone_verifications (username, phone, code, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $phone, $code, 'échoué']);

        $response['message'] = "❌ Code incorrect ou expiré.";
    }

} catch (Exception $e) {
    $response['message'] = "Erreur Twilio : " . $e->getMessage();
}

echo json_encode($response);