<?php
require '../config/database.php';
require_once '../vendor/autoload.php';

use Twilio\Rest\Client;

$config = require '../config/config.php';
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Méthode non autorisée.";
    echo json_encode($response);
    exit;
}

// reCAPTCHA
$recaptchaToken = $_POST['recaptcha_token'] ?? '';
$recaptchaSecret = $config['recaptcha']['secret_key'];

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaToken");
$captchaResult = json_decode($verify);

if (!$captchaResult->success || $captchaResult->score < 0.5) {
    $response['message'] = "Suspicion de bot détectée.";
    echo json_encode($response);
    exit;
}

// Données utilisateur
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'user';
$profil = $_POST['profil'] ?? 'novice';
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Validations serveur
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $response['message'] = "Nom d'utilisateur invalide.";
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = "Adresse email invalide.";
    echo json_encode($response);
    exit;
}

if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $password)) {
    $response['message'] = "Mot de passe trop faible.";
    echo json_encode($response);
    exit;
}

if ($phone && !preg_match('/^[0-9]{10}$/', $phone)) {
    $response['message'] = "Numéro de téléphone invalide.";
    echo json_encode($response);
    exit;
}

// Vérification unicité email
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetchColumn() > 0) {
    $response['message'] = "Email déjà utilisé.";
    echo json_encode($response);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$activationToken = bin2hex(random_bytes(32));

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, profil, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password_hash, $role, $profil, $email, $phone]);

    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO registration_attempts (ip_address, username, success) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $username, true]);

    $stmt = $pdo->prepare("INSERT INTO activation_tokens (username, token) VALUES (?, ?)");
    $stmt->execute([$username, $activationToken]);

    // Email de confirmation
    if ($email) {
        $subject = "Confirmation d'inscription au Quiz RSE";
        $headers = "From: {$config['email']['from']}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        $link = "https://rse-website.com/activate.php?token=$activationToken";
        $body = "Bonjour $username,\n\nMerci pour votre inscription !\nProfil : $profil\n\nActivez votre compte ici : $link";

        mail($email, $subject, $body, $headers);

        $stmt = $pdo->prepare("INSERT INTO email_logs (username, recipient, subject) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $subject]);
    }

    // SMS via Twilio
    if ($phone) {
        $client = new Client($config['twilio']['sid'], $config['twilio']['token']);
        try {
            $verification = $client->verify->v2->services($config['twilio']['verify_sid'])
                ->verifications
                ->create($phone, "sms");

            $status = $verification->status === "pending" ? "en attente" : "échec";
            $stmt = $pdo->prepare("INSERT INTO sms_logs (username, phone, message, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $phone, "Code de vérification envoyé", $status]);
        } catch (Exception $e) {
            error_log("Twilio Verify error: " . $e->getMessage());
        }
    }

    $response['success'] = true;
    $response['message'] = "✅ Utilisateur enregistré avec succès !";

} catch (PDOException $e) {
    $response['message'] = $e->getCode() == 23000 ? "Ce nom d'utilisateur existe déjà." : "Erreur : " . $e->getMessage();
    $stmt = $pdo->prepare("INSERT INTO registration_attempts (ip_address, username, success) VALUES (?, ?, ?)");
    $stmt->execute([$_SERVER['REMOTE_ADDR'], $username, false]);
}

echo json_encode($response);