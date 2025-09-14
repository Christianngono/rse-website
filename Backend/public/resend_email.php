<?php
require '../config/database.php';
header('Content-Type: application/json');
session_start();

// Vérifier les droits d'accès (uniquement admin et user)
if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

// Récupérer l'email original
$stmt = $pdo->prepare("SELECT * FROM email_logs WHERE id = ?");
$stmt->execute([$id]);
$email = $stmt->fetch();

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email introuvable']);
    exit;
}

// Réenvoyer l'email
$to = $email['recipient'];
$subject = "[Réexpédition] " . $email['subject'];
$message = "Bonjour,\n\nVoici une réexpédition de l'email original envoyé par {$email['username']}.\n\nCordialement,\nL'équipe RSE";
$headers = "From: rse@quiz.com\r\n";

$mailSent = mail($to, $subject, $message, $headers);

// Archiver le réenvoi
if ($mailSent) {
    $stmt = $pdo->prepare("INSERT INTO email_logs (username, recipient, subject, sent_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user'], $to, $subject]);
}

echo json_encode([
    'success' => $mailSent,
    'message' => $mailSent ? 'Email réexpédié avec succès' : 'Échec de la réexpédition'
]);