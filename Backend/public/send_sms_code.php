<?php
require '../../config/database.php';
require_once '../../vendor/autoload.php';

use Twilio\Rest\Client;

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

$phone = $_POST['phone'] ?? '';

if (!$phone) {
    $response['message'] = "Numéro requis.";
    echo json_encode($response);
    exit;
}

$config = require '../../config/config.php';
$sid = $config['twilio']['sid'];
$token = $config['twilio']['token'];
$verifySid = $config['twilio']['verify_sid'];

$client = new Client($sid, $token);

try {
    $client->verify->v2->services($verifySid)
        ->verifications
        ->create(['to' => $phone, 'channel' => 'sms']);

    $response['success'] = true;
    $response['message'] = "📲 Code envoyé par SMS.";
} catch (Exception $e) {
    $response['message'] = "Erreur Twilio : " . $e->getMessage();
}

echo json_encode($response);