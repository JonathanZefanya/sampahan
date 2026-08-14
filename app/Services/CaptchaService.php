<?php

namespace App\Services;

use App\Models\SettingModel;
use CodeIgniter\Session\Session;


class CaptchaService
{
    private string  $provider;
    private string  $secretKey;
    private string  $siteKey;
    private Session $session;

    private const SESSION_KEY    = 'captcha_answer';
    private const SESSION_EXPIRY = 'captcha_expiry';
    private const SESSION_QUESTION = 'captcha_question';
    private const SESSION_ATTEMPTS = 'captcha_attempts';
    private const TTL            = 600; // 10 minutes
    private const MAX_ATTEMPTS   = 5;

    public function __construct()
    {
        $settings = (new SettingModel())->getAll();

        $this->provider  = $settings['captcha_provider']   ?? 'none';
        $this->secretKey = $settings['captcha_secret_key'] ?? '';
        $this->siteKey   = $settings['captcha_site_key']   ?? '';
        $this->session   = \Config\Services::session();
    }

    //  Public API 

    /**
     * True when captcha is properly configured and should be enforced.
     * selfhosted only requires provider = 'selfhosted' (no API keys needed).
     */
    public function isEnabled(): bool
    {
        if ($this->provider === 'none' || $this->provider === '') {
            return false;
        }

        if ($this->provider === 'selfhosted') {
            return true; // built-in math captcha  no API keys required
        }

        // External providers need both site key AND secret key
        return $this->secretKey !== '' && $this->siteKey !== '';
    }

    public function getProvider(): string { return $this->provider; }
    public function getSiteKey(): string  { return $this->siteKey; }

    public function generateChallenge(): string
    {
        if ($this->provider !== 'selfhosted') {
            return '';
        }

        $ops = ['+', '-', 'x'];
        $op  = $ops[array_rand($ops)];

        switch ($op) {
            case '+':
                $a = random_int(1, 20);
                $b = random_int(1, 20);
                $ans = $a + $b;
                break;
            case '-':
                $a = random_int(5, 20);
                $b = random_int(1, $a);
                $ans = $a - $b;
                break;
            default: // multiply
                $a = random_int(2, 9);
                $b = random_int(2, 9);
                $ans = $a * $b;
                break;
        }

        $this->session->set(self::SESSION_KEY,    (string) $ans);
        $this->session->set(self::SESSION_EXPIRY, time() + self::TTL);
        $this->session->set(self::SESSION_QUESTION, "{$a} {$op} {$b}");
        $this->session->set(self::SESSION_ATTEMPTS, 0);

        return "{$a} {$op} {$b}";
    }

    public function verify(string $token, string $ip = ''): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        if (trim($token) === '') {
            return false;
        }

        if ($this->provider === 'selfhosted') {
            return $this->verifySelfHosted($token);
        }

        $verifyUrl = match ($this->provider) {
            'recaptcha' => 'https://www.google.com/recaptcha/api/siteverify',
            'turnstile' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            default     => '',
        };

        if ($verifyUrl === '') {
            return true;
        }

        $payload = http_build_query([
            'secret'   => $this->secretKey,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 5,
            ],
        ]);

        $result = @file_get_contents($verifyUrl, false, $context);
        if ($result === false) {
            return false;
        }

        $json = json_decode($result, true);
        return (bool) ($json['success'] ?? false);
    }

    public function clearChallenge(): void
    {
        $this->session->remove(self::SESSION_KEY);
        $this->session->remove(self::SESSION_EXPIRY);
        $this->session->remove(self::SESSION_QUESTION);
        $this->session->remove(self::SESSION_ATTEMPTS);
    }

    public function widgetHtml(string $question = ''): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        return match ($this->provider) {
            'recaptcha'  => $this->recaptchaWidget(),
            'turnstile'  => $this->turnstileWidget(),
            'selfhosted' => $this->selfhostedWidget($question),
            default      => '',
        };
    }

    private function verifySelfHosted(string $token): bool
    {
        $expected = $this->session->get(self::SESSION_KEY);
        $expiry   = $this->session->get(self::SESSION_EXPIRY);

        if ($expected === null) {
            return false;
        }

        if (time() > (int) $expiry) {
            $this->clearChallenge(); 
            return false;
        }

        if (hash_equals(trim((string) $expected), trim($token))) {
            return true;
        }

        $attempts = (int) ($this->session->get(self::SESSION_ATTEMPTS) ?? 0) + 1;
        $this->session->set(self::SESSION_ATTEMPTS, $attempts);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->clearChallenge(); // too many wrong answers -> new question
        }

        return false;
    }

    private function recaptchaWidget(): string
    {
        return <<<HTML
<div class="mb-3">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <div class="g-recaptcha" data-sitekey="{$this->siteKey}"></div>
</div>
HTML;
    }

    private function turnstileWidget(): string
    {
        return <<<HTML
<div class="mb-3">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <div class="cf-turnstile" data-sitekey="{$this->siteKey}"></div>
</div>
HTML;
    }

    private function selfhostedWidget(string $question): string
    {
        if ($question === '') {
            $question = '? + ?';
        }

        return <<<HTML
<div class="mb-3">
    <div class="p-3 border rounded-3 bg-light d-flex align-items-center gap-3">
        <div class="bg-success bg-opacity-10 border border-success rounded-3 px-4 py-2 text-center flex-shrink-0">
            <span class="fs-5 fw-bold text-success font-monospace">{$question}</span>
            <div class="text-muted" style="font-size:11px;">= ?</div>
        </div>
        <div class="flex-grow-1">
            <input type="number" name="captcha_answer" id="captchaAnswer"
                   class="form-control" placeholder="Masukkan hasil"
                   autocomplete="off" required>
            <small class="text-muted">Hitung dan masukkan hasilnya.</small>
        </div>
    </div>
</div>
HTML;
    }
}