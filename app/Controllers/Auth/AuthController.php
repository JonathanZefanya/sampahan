<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\SettingModel;
use App\Models\UserModel;
use App\Services\DynamicMailer;

class AuthController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function login(): mixed
    {
        if (session()->has('user')) {
            return $this->redirectByRole();
        }

        return $this->view('auth/login');
    }

    public function attempt()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->attemptLogin($email, $password);

        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        if (! $user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Akun dinonaktifkan Admin.');
        }

        if (empty($user['email_verified_at'])) {
            return redirect()->back()->withInput()->with('error', 'Akun belum diverifikasi. Cek email Anda.');
        }

        // Persist session (never store raw password)
        session()->set('user', [
            'id'        => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'is_active' => $user['is_active'],
        ]);

        return $this->redirectByRole();
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'Anda telah logout.');
    }

    // ─── Register ─────────────────────────────────────────────────────────────

    public function register(): string
    {
        return $this->view('auth/register');
    }

    public function store()
    {
        $rules = [
            'name'             => 'required|min_length[3]|max_length[150]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settingModel      = new SettingModel();
        $emailVerification = (bool) $settingModel->get('enable_email_verification', 0);

        $userData = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'     => 'masyarakat',
            'is_active'=> $emailVerification ? 0 : 1,
        ];

        if (! $emailVerification) {
            $userData['email_verified_at'] = date('Y-m-d H:i:s');
        }

        $userId = $this->userModel->insert($userData);

        if (! $userId) {
            return redirect()->back()->withInput()->with('error', 'Registrasi gagal. Coba lagi.');
        }

        if ($emailVerification) {
            $token = $this->userModel->generateActivationToken((int) $userId);
            try {
                (new DynamicMailer())->sendActivation($userData['email'], $userData['name'], $token);
            } catch (\Throwable $e) {
                log_message('error', '[AuthController::store] Mailer: ' . $e->getMessage());
            }

            return redirect()->to('/auth/login')
                ->with('success', 'Registrasi berhasil! Cek email Anda untuk aktivasi akun.');
        }

        return redirect()->to('/auth/login')
            ->with('success', 'Registrasi berhasil! Silakan login.');
    }

    // ─── Email Activation ─────────────────────────────────────────────────────

    public function activate(string $token)
    {
        if ($this->userModel->activateByToken($token)) {
            return redirect()->to('/auth/login')
                ->with('success', 'Akun berhasil diaktivasi. Silakan login.');
        }

        return redirect()->to('/auth/login')
            ->with('error', 'Token aktivasi tidak valid atau sudah digunakan.');
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function redirectByRole(): \CodeIgniter\HTTP\RedirectResponse
    {
        $role = session()->get('user')['role'] ?? 'masyarakat';

        return match ($role) {
            'admin'      => redirect()->to('/admin/dashboard'),
            'dinas'      => redirect()->to('/dinas/dashboard'),
            default      => redirect()->to('/masyarakat/dashboard'),
        };
    }
}
