<?php
header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

// Vérification de session
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

$username = $_SESSION['user'];

// Requête : nombre de bonnes réponses par catégorie
$stmt = $pdo->prepare("
    SELECT q.category, COUNT(*) AS score
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    WHERE ua.user_name = ? AND ua.is_correct = 1
    GROUP BY q.category
");
$stmt->execute([$username]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour le graphique
$categories = ['Énergie', 'Transport', 'Alimentation', 'Déchets', 'Eau'];
$data = [];

foreach ($categories as $cat) {
    $found = false;
    foreach ($results as $row) {
        if ($row['category'] === $cat) {
            $data[] = (int)$row['score'];
            $found = true;
            break;
        }
    }
    if (!$found) $data[] = 0;
}

echo json_encode([
    'labels' => $categories,
    'data' => $data
]);