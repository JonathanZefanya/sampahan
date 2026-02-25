<?php

namespace App\Services;

use App\Models\SettingModel;
use CodeIgniter\Email\Email;

/**
 * DynamicMailer
 *
 * Loads SMTP credentials at runtime from the `settings` table –
 * NEVER from .env or Config/Email.php.
 *
 * Usage:
 *   $mailer = new \App\Services\DynamicMailer();
 *   $mailer->send(
 *       to      : 'user@example.com',
 *       subject : 'Aktivasi Akun',
 *       body    : view('emails/activation', $data),
 *   );
 */
class DynamicMailer
{
    private Email $email;

    public function __construct()
    {
        $settingModel = new SettingModel();
        $config       = $settingModel->getMailConfig();

        $this->email = \Config\Services::email();
        $this->email->initialize($config);
    }

    /**
     * Send an email.
     *
     * @throws \RuntimeException on send failure
     */
    public function send(
        string $to,
        string $subject,
        string $body,
        ?string $altMessage = null
    ): bool {
        $this->email->setTo($to);
        $this->email->setSubject($subject);
        $this->email->setMessage($body);

        if ($altMessage) {
            $this->email->setAltMessage($altMessage);
        }

        if (! $this->email->send()) {
            log_message('error', '[DynamicMailer] Failed to send to ' . $to . ': ' . $this->email->printDebugger(['headers']));
            return false;
        }

        return true;
    }

    /**
     * Send account activation email.
     */
    public function sendActivation(string $toEmail, string $toName, string $token): bool
    {
        $link    = base_url('auth/activate/' . $token);
        $appName = (new SettingModel())->get('app_name', 'SAMPAHAN');

        $body = view('emails/activation', [
            'name'    => $toName,
            'link'    => $link,
            'appName' => $appName,
        ]);

        return $this->send($toEmail, "Aktivasi Akun {$appName}", $body);
    }

    /**
     * Send forgot-password reset link.
     */
    public function sendPasswordReset(string $toEmail, string $toName, string $token): bool
    {
        $link    = base_url('auth/reset-password/' . $token);
        $appName = (new SettingModel())->get('app_name', 'SAMPAHAN');

        $body = view('emails/password_reset', [
            'name'    => $toName,
            'link'    => $link,
            'appName' => $appName,
        ]);

        return $this->send($toEmail, "Reset Password – {$appName}", $body);
    }

    /**
     * Send "Thank You" notification when a report is marked Cleaned.
     */
    public function sendCleanedNotification(string $toEmail, string $toName, int $reportId): bool
    {
        $appName = (new SettingModel())->get('app_name', 'SAMPAHAN');
        $link    = base_url('masyarakat/history');

        $body = view('emails/cleaned_notification', [
            'name'     => $toName,
            'reportId' => $reportId,
            'link'     => $link,
            'appName'  => $appName,
        ]);

        return $this->send($toEmail, "Laporan #{$reportId} Telah Dibersihkan – {$appName}", $body);
    }
}
