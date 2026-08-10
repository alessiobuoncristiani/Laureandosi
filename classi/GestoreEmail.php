<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
require_once 'FileConfigurazione.php';

class GestoreEmail
{
    private int $delaySpam;
    private string $oggettoEmail;
    private string $corpoEmail;

    public function __construct(string $oggettoEmail, string $corpoEmail, int $delaySpam)
    {
        $this->oggettoEmail = $oggettoEmail;
        $this->corpoEmail = $corpoEmail;
        $this->delaySpam = $delaySpam;
    }

    // La configurazione arriva qui (Method Injection)
    public function inviaEmailConAllegato(string $destinatario, string $pathAllegato, FileConfigurazione $config): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();

            $mail->Host       = $config->getEmailHost();
            $mail->SMTPAuth   = false;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 25;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($config->getEmailFromMail(), $config->getEmailFromName());
            $mail->addAddress($destinatario);

            $mail->Subject = $this->oggettoEmail;
            $mail->Body    = $this->corpoEmail;

            if (file_exists($pathAllegato)) {
                $mail->addAttachment($pathAllegato);
            } else {
                throw new Exception("Allegato non trovato: " . $pathAllegato);
            }

            $mail->send();
            $mail->smtpClose();

            return true;
        } catch (Exception $e) {
            error_log("Errore invio mail a $destinatario: " . $mail->ErrorInfo);
            return false;
        }
    }
}