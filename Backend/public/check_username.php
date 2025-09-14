<?php
require '../config/database.php';
header('Content-Type: application/json');

$response = [
    'username_available' => false,
    'email_available' => false,
    'message' => ''
];

// Vérification du nom d'utilisateur
if (isset($_GET['username'])) {
    $username = trim($_GET['username']);
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $response['message'] = "Nom d'utilisateur invalide.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $response['username_available'] = true;
                $response['message'] = "Nom disponible ✅";
            } else {
                $suggestions = [];
                for ($i = 1; $i <= 3; $i++) {
                    $suggestions[] = $username . rand(100, 999);
                }
                $response['message'] = "Nom déjà pris ❌. Suggestions : " . implode(', ', $suggestions);
            }
        } catch (PDOException $e) {
            error_log("Erreur DB (username) : " . $e->getMessage());
            $response['message'] = "Erreur serveur lors de la vérification du nom.";
        }
    }
}

// Vérification de l'email
if (isset($_GET['email'])) {
    $email = trim($_GET['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] .= " Format d'email invalide.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $response['email_available'] = true;
                $response['message'] .= " Email disponible ✅";
            } else {
                $response['message'] .= " Email déjà utilisé ❌";
            }
        } catch (PDOException $e) {
            error_log("Erreur DB (email) : " . $e->getMessage());
            $response['message'] .= " Erreur serveur lors de la vérification de l'email.";
        }
    }
}

echo json_encode($response);