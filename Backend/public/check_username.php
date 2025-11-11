<?php
require '../includes/init.php';
header('Content-Type: application/json');

$response = [
    'username_available' => null,
    'email_available' => null,
    'username_message' => '',
    'email_message' => ''
];

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');

// Vérification du nom d'utilisateur
if ($username !== '') {
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $response['username_available'] = false;
        $response['username_message'] = "Nom d'utilisateur invalide.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $response['username_available'] = true;
                $response['username_message'] = "Nom disponible ✅";
            } else {
                $response['username_available'] = false;
                $suggestions = [];
                for ($i = 1; $i <= 3; $i++) {
                    $suggestions[] = $username . rand(100, 999);
                }
                $response['username_message'] = "Nom déjà pris ❌. Suggestions : " . implode(', ', $suggestions);
            }
        } catch (PDOException $e) {
            error_log("Erreur DB (username) : " . $e->getMessage());
            $response['username_available'] = false;
            $response['username_message'] = "Erreur serveur lors de la vérification du nom.";
        }
    }
}

// 📧 Vérification de l'email
if ($email !== '') {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['email_available'] = false;
        $response['email_message'] = "Format d'email invalide.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $response['email_available'] = true;
                $response['email_message'] = "Email disponible ✅";
            } else {
                $response['email_available'] = false;
                $response['email_message'] = "Email déjà utilisé ❌";
            }
        } catch (PDOException $e) {
            error_log("Erreur DB (email) : " . $e->getMessage());
            $response['email_available'] = false;
            $response['email_message'] = "Erreur serveur lors de la vérification de l'email.";
        }
    }
}

echo json_encode($response);