<?php
session_start();

// Vérification des droits d'accès
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Accès refusé.";
    exit;
}

// Connexion à la base de données
require_once '../config/database.php';

// Chargement de FPDF et définition de la classe PDF
require_once '../lib/fpdf186/fpdf186/fpdf.php';

class PDF extends FPDF {
    function Header() {
        $this->Image('../Frontend/assets/images/logo.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Statistiques RSE des utilisateurs', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | Généré le ' . date('d/m/Y à H:i'), 0, 0, 'C');
    }
}

// Chargement du module de diagramme
require_once '../lib/fpdf186/fpdf186/diag.php'; // PDF_Diag étend PDF

// Création du PDF
$pdf = new PDF_Diag();
$pdf->AddPage();

// Titre du graphique
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Taux de réussite par utilisateur', 0, 1, 'C');
$pdf->Ln(5);

// Données du graphique
$graphData = [];
$stmtGraph = $pdo->query("
    SELECT ua.user_name,
           ROUND(SUM(ua.is_correct)/COUNT(*)*100, 2) AS success_rate
    FROM user_answers ua
    GROUP BY ua.user_name
");
while ($row = $stmtGraph->fetch(PDO::FETCH_ASSOC)) {
    $graphData[$row['user_name']] = $row['success_rate'];
}
$pdf->BarDiagram(180, 70, $graphData, '%', [100, 150, 255]);
$pdf->Ln(10);

// Tableau principal
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 240, 255);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(180, 180, 180);
$pdf->SetLineWidth(0.3);

$pdf->Cell(40, 10, 'Utilisateur', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Catégorie', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Réponses', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Correctes', 1, 0, 'C', true);
$pdf->Cell(30, 10, '% Réussite', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 11);
$stmt = $pdo->query("
    SELECT ua.user_name, q.category,
           COUNT(*) AS total,
           SUM(ua.is_correct) AS correct,
           ROUND(SUM(ua.is_correct)/COUNT(*)*100, 2) AS success_rate
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    GROUP BY ua.user_name, q.category
");

$users = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pdf->Cell(40, 10, $row['user_name'], 1);
    $pdf->Cell(40, 10, $row['category'], 1);
    $pdf->Cell(30, 10, $row['total'], 1);
    $pdf->Cell(30, 10, $row['correct'], 1);
    $pdf->Cell(30, 10, $row['success_rate'] . '%', 1);
    $pdf->Ln();
    $users[$row['user_name']] = true;
}

// Statistiques RSE spécifiques
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Statistiques RSE spécifiques', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(50, 10, 'Utilisateur', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Correctes', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Incorrectes', 1, 0, 'C', true);
$pdf->Cell(40, 10, '% Réussite', 1, 1, 'C', true);

foreach (array_keys($users) as $user) {
    $rseCorrect = $pdo->prepare("SELECT COUNT(*) FROM user_answers WHERE user_name = ? AND is_correct = TRUE");
    $rseCorrect->execute([$user]);
    $correct = $rseCorrect->fetchColumn();

    $rseWrong = $pdo->prepare("SELECT COUNT(*) FROM user_answers WHERE user_name = ? AND is_correct = FALSE");
    $rseWrong->execute([$user]);
    $wrong = $rseWrong->fetchColumn();

    $total = $correct + $wrong;
    $rate = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

    $pdf->Cell(50, 10, $user, 1);
    $pdf->Cell(40, 10, $correct, 1);
    $pdf->Cell(40, 10, $wrong, 1);
    $pdf->Cell(40, 10, $rate . '%', 1);
    $pdf->Ln();
}

// Export du fichier PDF
$pdf->Output('D', 'stats_rse_utilisateurs.pdf');