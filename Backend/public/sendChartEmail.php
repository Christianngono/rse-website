<?php
header('Content-Type: application/json');
require '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$imageData = $data['image'] ?? '';
$username = $data['user'] ?? 'Utilisateur';
$emailTo = $data['emailTo'] ?? '';
$subject = $data['subject'] ?? "Statistiques RSE de $username";
$message = $data['message'] ?? "Veuillez trouver ci-joint le graphique des statistiques.";

if (!$imageData || !$emailTo) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

// Sauvegarde temporaire
$img = str_replace('data:image/png;base64,', '', $imageData);
$img = str_replace(' ', '+', $img);
$binary = base64_decode($img);
$filePath = "../temp/chart_{$username}_" . date('Ymd_His') . ".png";
file_put_contents($filePath, $binary);

// Préparation email
$file = chunk_split(base64_encode(file_get_contents($filePath)));
$uid = md5(uniqid(time()));
$name = basename($filePath);

$header = "From: rse@quiz.com\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-type:text/plain; charset=utf-8\r\n";
$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$header .= $message."\r\n\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-Type: image/png; name=\"".$name."\"\r\n";
$header .= "Content-Transfer-Encoding: base64\r\n";
$header .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
$header .= $file."\r\n\r\n";
$header .= "--".$uid."--";

// Envoi
$mailSent = mail($emailTo, $subject, "", $header);

// Archivage en base
if ($mailSent) {
    $stmt = $pdo->prepare("INSERT INTO email_logs (username, recipient, subject, sent_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$username, $emailTo, $subject]);
}

unlink($filePath);

echo json_encode([
  'success' => $mailSent,
  'message' => $mailSent ? 'Email envoyé avec succès' : 'Échec de l’envoi'
]);