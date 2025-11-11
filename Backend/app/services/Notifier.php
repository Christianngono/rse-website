<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twilio\Rest\Client;
use Dotenv\Dotenv;

class Notifier {
    private $pdo;
    private $twilioSid;
    private $twilioToken;
    private $twilioFrom;
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
        $dotenv->load();

        $this->twilioSid   = $_ENV['TWILIO_SID'];
        $this->twilioToken = $_ENV['TWILIO_TOKEN'];
        $this->twilioFrom  = $_ENV['TWILIO_FROM'];
        $this->smtpHost    = $_ENV['SMTP_HOST'];
        $this->smtpPort    = $_ENV['SMTP_PORT'];
        $this->smtpUser    = $_ENV['SMTP_USER'];
        $this->smtpPass    = $_ENV['SMTP_PASS'];
    }

    public function notifyUser($userId, $subject, $message, $attachmentPath = null) {
        $stmt = $this->pdo->prepare("SELECT email, phone FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $this->sendEmail($user['email'], $subject, $message, $attachmentPath);
            $this->sendSMS($user['phone'], $message);
        }
    }

    public function sendEmail($to, $subject, $body, $attachmentPath = null) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUser;
            $mail->Password   = $this->smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $this->smtpPort;

            $mail->setFrom($this->smtpUser, 'Plateforme RSE');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            if ($attachmentPath && file_exists($attachmentPath)) {
                $mail->addAttachment($attachmentPath);
            }

            $mail->send();

            $stmt = $this->pdo->prepare("INSERT INTO email_logs (username, recipient, subject, sent_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute(['system', $to, $subject]);

        } catch (Exception $e) {
            error_log("PHPMailer error: " . $mail->ErrorInfo);
        }
    }

    public function sendSMS($to, $message) {
        try {
            $client = new Client($this->twilioSid, $this->twilioToken);
            $client->messages->create($to, [
                'from' => $this->twilioFrom,
                'body' => $message
            ]);
        } catch (Exception $e) {
            error_log("Twilio error: " . $e->getMessage());
        }
    }
}