<?php
session_start();
require_once '../config/database.php';
require_once '../lib/fpdf/fpdf.php';

// Vérification d'accès admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    http_response_code(403);
    echo "Accès refusé.";
    exit;
}

// Récupération des questions
try {
    $stmt = $pdo->query("SELECT * FROM questions ORDER BY category, difficulty");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur de base de données : " . $e->getMessage();
    exit;
}

// Format demandé
$allowedFormats = ['json', 'csv', 'pdf'];
$format = in_array($_GET['format'] ?? 'json', $allowedFormats) ? $_GET['format'] : 'json';

// Export CSV
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="questions_' . date('Ymd_His') . '.csv"');

    $fp = fopen('php://output', 'w');
    fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); 
    fputcsv($fp, ['ID', 'Question', 'Réponse correcte', 'Catégorie', 'Difficulté']);

    foreach ($questions as $q) {
        fputcsv($fp, [
            $q['id'],
            strip_tags($q['question_text']),
            $q['correct_answer'],
            $q['category'],
            $q['difficulty']
        ]);
    }

    fclose($fp);
    exit;
}

// Export PDF
if ($format === 'pdf') {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Liste des questions', 0, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    foreach ($questions as $q) {
        $pdf->MultiCell(0, 10, "Q{$q['id']}: " . strip_tags($q['question_text']));
        $pdf->Cell(0, 10, "Réponse: " . $q['correct_answer'], 0, 1);
        $pdf->Cell(0, 10, "Catégorie: " . $q['category'] . " | Difficulté: " . $q['difficulty'], 0, 1);
        $pdf->Ln(5);
    }

    $pdf->Output('D', 'questions_' . date('Ymd_His') . '.pdf');
    exit;
}

// Export JSON (par défaut)
header('Content-Type: application/json; charset=utf-8');
echo json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);