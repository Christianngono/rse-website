<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=rse_quiz', 'admin_chris', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération du nom d'utilisateur
$user = $_GET['user'] ?? 'test_user';
$user = htmlspecialchars($user);

// Statistiques globales
$global = $pdo->prepare("
    SELECT COUNT(*) AS total,
           SUM(is_correct) AS correct,
           ROUND(SUM(is_correct)/COUNT(*)*100, 2) AS success_rate
    FROM user_answers
    WHERE user_name = ?
");
$global->execute([$user]);
$globalStats = $global->fetch();

// Statistiques par catégorie
$categories = $pdo->prepare("
    SELECT q.category,
           COUNT(*) AS total,
           SUM(ua.is_correct) AS correct,
           ROUND(SUM(ua.is_correct)/COUNT(*)*100, 2) AS success_rate
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    WHERE ua.user_name = ?
    GROUP BY q.category
");
$categories->execute([$user]);

// Réponses RSE correctes
$rseCorrect = $pdo->prepare("
    SELECT COUNT(*) AS rse_correct
    FROM rse_answers
    WHERE user_name = ?
");
$rseCorrect->execute([$user]);
$rseCorrectCount = $rseCorrect->fetchColumn();

// Réponses RSE incorrectes
$rseWrong = $pdo->prepare("
    SELECT COUNT(*) AS rse_wrong
    FROM rse_wrong_answers
    WHERE user_name = ?
");
$rseWrong->execute([$user]);
$rseWrongCount = $rseWrong->fetchColumn();

// Taux de réussite RSE
$rseTotal = $rseCorrectCount + $rseWrongCount;
$rseRate = $rseTotal > 0 ? round(($rseCorrectCount / $rseTotal) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques RSE - <?= $user ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 70%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .highlight { background-color: #e6ffe6; }
    </style>
</head>
<body>

<h2>Statistiques pour l'utilisateur : <strong><?= $user ?></strong></h2>

<h3>Résultats généraux</h3>
<ul>
    <li><strong>Total de réponses :</strong> <?= $globalStats['total'] ?></li>
    <li><strong>Réponses correctes :</strong> <?= $globalStats['correct'] ?></li>
    <li><strong>Taux de réussite global :</strong> <?= $globalStats['success_rate'] ?>%</li>
</ul>

<h3>Répartition par catégorie</h3>
<table>
    <tr>
        <th>Catégorie</th>
        <th>Réponses</th>
        <th>Correctes</th>
        <th>% Réussite</th>
    </tr>
    <?php foreach ($categories as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= $row['total'] ?></td>
            <td><?= $row['correct'] ?></td>
            <td><?= $row['success_rate'] ?>%</td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Statistiques RSE</h3>
<table>
    <tr>
        <th>Réponses RSE correctes</th>
        <th>Réponses RSE incorrectes</th>
        <th>Total</th>
        <th>% Réussite RSE</th>
    </tr>
    <tr class="highlight">
        <td><?= $rseCorrectCount ?></td>
        <td><?= $rseWrongCount ?></td>
        <td><?= $rseTotal ?></td>
        <td><?= $rseRate ?>%</td>
    </tr>
</table>

</body>
</html>