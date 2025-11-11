<?php
declare(strict_types=1);
require_once '../config/database.php';
require_once '../lib/fpdf186/fpdf186/fpdf.php';

// === Lecture de la configuration dynamique ===
$config = $pdo->query("SELECT * FROM report_config WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
if (!$config) exit("Pas de configuration trouvée.");

$recipient = $config['email'];
$frequency = $config['frequency'];
$format    = $config['format']; // 'pdf', 'csv', 'both'

// === Définition de l'intervalle de temps ===
$interval = $frequency === 'weekly' ? '7 DAY' : '1 DAY';

$sql = "SELECT admin_username, action, target_user_id, timestamp 
        FROM admin_logs 
        WHERE timestamp >= NOW() - INTERVAL $interval 
        ORDER BY timestamp DESC";
$stmt = $pdo->query($sql);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$logs) exit("Aucun log à envoyer.");

// === Génération des pièces jointes ===
$attachments = [];
$boundary = md5(uniqid());

// CSV
if ($format === 'csv' || $format === 'both') {
    $csvPath = sys_get_temp_dir() . "/admin_logs_$frequency.csv";
    $csvFile = fopen($csvPath, 'w');
    fputcsv($csvFile, ['Date', 'Administrateur', 'Action', 'ID cible']);
    foreach ($logs as $log) {
        fputcsv($csvFile, [$log['timestamp'], $log['admin_username'], $log['action'], $log['target_user_id']]);
    }
    fclose($csvFile);
    $attachments[] = [
        'path' => $csvPath,
        'type' => 'text/csv',
        'name' => "admin_logs_$frequency.csv"
    ];
}

// PDF
if ($format === 'pdf' || $format === 'both') {
    $pdfPath = sys_get_temp_dir() . "/admin_logs_$frequency.pdf";
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, "Journal des actions admin - $frequency", 0, 1);
    $pdf->SetFont('Arial', '', 11);
    foreach ($logs as $log) {
        $line = "{$log['timestamp']} | {$log['admin_username']} | {$log['action']} | ID: {$log['target_user_id']}";
        $pdf->MultiCell(0, 8, $line);
    }
    $pdf->Output('F', $pdfPath);
    $attachments[] = [
        'path' => $pdfPath,
        'type' => 'application/pdf',
        'name' => "admin_logs_$frequency.pdf"
    ];
}

// === Construction du message email ===
$headers = "From: rse@quiz.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

$body = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$body .= "Veuillez trouver ci-joint le journal des actions admin ($frequency).\r\n\r\n";

// === Ajout des pièces jointes ===
foreach ($attachments as $file) {
    $content = chunk_split(base64_encode(file_get_contents($file['path'])));
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: {$file['type']}; name=\"{$file['name']}\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-Disposition: attachment; filename=\"{$file['name']}\"\r\n\r\n";
    $body .= $content . "\r\n";
}

// === Fin du message ===
$body .= "--$boundary--";

// === Envoi de l'email ===
$sent = mail($recipient, "Rapport $frequency des actions admin", $body, $headers);

// === Nettoyage des fichiers temporaires ===
foreach ($attachments as $file) {
    unlink($file['path']);
}

// === Enregistrement dans report_history ===
$stmt = $pdo->prepare("INSERT INTO report_history (recipient, format, frequency, status, message) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([
    $recipient,
    $format,
    $frequency,
    $sent ? 'success' : 'failed',
    $sent ? 'Email envoyé avec pièces jointes' : 'Échec de l’envoi'
]);

echo $sent ? "Email envoyé à $recipient" : "Échec de l’envoi.";