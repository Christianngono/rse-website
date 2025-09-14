<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../config/database.php';
require_once '../lib/fpdf186/fpdf186/fpdf.php';
require_once '../../../vendor/autoload.php';

$username = $_POST['username'] ?? 'Participant';
$email = $_POST['email'] ?? 'email@example.com';
$score = $_POST['score'] ?? 0;
$date = date("d/m/Y");

// Générer le certificat PDF
$pdf = new FPDF();
$pdf->AddPage('L');

// mettre Fond d’image
$pdf->Image('../../Frontend/assets/images/certificat_background.jpg', 0, 0, 297, 210);

// mettre un Logo en haut à gauche
$pdf->Image('../../Frontend/assets/images/logo.png', 10, 10, 30);

// Ajouter un titre
$pdf->SetXY(50, 140);
$pdf->SetFont('Arial', 'B', 24);
$pdf->Cell(0, 30, "Certificat de Réussite RSE", 0, 1, 'C');


// Ajouter le Nom du participant
$pdf->SetXY(0, 100);
$pdf->SetFont('Arial', 'B', 24);
$pdf->Cell(297, 10, strtoupper($nom), 0, 1, 'C');

// Scorer 
$pdf->SetXY(0, 110);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(297, 10, "Score : $score/20", 0, 1, 'C');

// Dater
$pdf->SetXY(0, 120);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(297, 10, "Date : $date", 0, 1, 'C');

// Ajouter le texte descriptif (le score et la date)
$pdf->SetXY(30, 135);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(237, 8, "Ce certificat est décerné à $nom pour avoir obtenu un score de $score/20 au quiz RSE, le $date.", 0, 'J');

// Reserver l'espace vide pour signature manuelle
$pdf->SetXY(220, 180);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(60, 10, "Signature du participant :", 0, 1, 'L');
$pdf->Line(220, 195, 280, 195); // ligne pour signer

// Enregistrer du certificat
$cheminCertificat = "../../../certificats/certificat_" . str_replace(' ', '_', $nom) . ".pdf";
$pdf->Output('F', $cheminCertificat);

// Envoi par mail
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
    $mail->addAddress($email, $nom);
    $mail->isHTML(true);
    $mail->Subject = 'Votre certificat RSE';
    $mail->Body = "<p>Bonjour $nom,</p><p>Félicitations pour votre score de <strong>$score/20</strong> au quiz RSE !</p><p>Veuillez trouver votre certificat en pièce jointe.</p>";
    $mail->addAttachment($cheminCertificat);
    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Certificat envoyé']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
}