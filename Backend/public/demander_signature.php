<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Chargement des dépendances
require_once '../config/database.php';
require_once '../../../vendor/autoload.php';

// Récupération des données JSON
$data = json_decode(file_get_contents("php://input"), true);
$username = htmlspecialchars($data['username'] ?? '');
$email = htmlspecialchars($data['email'] ?? '');
$score = intval($data['score'] ?? 0);

// Vérification des données
if (!$email || !$username || $score < 0) {
    echo json_encode(['success' => false, 'message' => 'Email, nom et score requis']);
    exit;
}

// Génération du code et du token
$code = rand(100000, 999999);
$token = bin2hex(random_bytes(16));
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Insertion en base de données
try {
    $stmt = $pdo->prepare("INSERT INTO signature_tokens (username, email, code, token, expires_at, score) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $email, $code, $token, $expires, $score]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
    exit;
}

// Envoi de l’email via PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ton.email@gmail.com';
    $mail->Password = 'mot_de_passe_application';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('contact@rse-website.com', 'RSE Website');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = '🔐 Validez votre signature numérique';

    // Lien de validation
    $lien = "https://rse-website.com/valider_signature.html?token=$token";

    $mail->Body = "
        Bonjour $username,<br><br>
        Votre code de signature est : <strong>$code</strong><br>
        Cliquez ici pour valider votre certificat : <a href='$lien'>Valider ma signature</a><br><br>
        Ce lien expire dans 10 minutes.
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => "Lien de validation envoyé à $email"]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur email : ' . $mail->ErrorInfo]);
}