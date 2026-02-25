<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\DynamicMailer;

class ForgotPasswordController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index(): string
    {
        return $this->view('auth/forgot_password');
    }

    public function send()
    {
        $email = $this->request->getPost('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Email tidak valid.');
        }

        $token = $this->userModel->generateResetToken($email);

        // Always show the same message to prevent email enumeration
        if ($token) {
            $user = $this->userModel->findByEmail($email);
            try {
                (new DynamicMailer())->sendPasswordReset($email, $user['name'] ?? '', $token);
            } catch (\Throwable $e) {
                log_message('error', '[ForgotPassword::send] ' . $e->getMessage());
            }
        }

        return redirect()->to('/auth/forgot-password')
            ->with('success', 'Jika email terdaftar, link reset password telah dikirim.');
    }

    public function reset(string $token): string
    {
        $user = $this->userModel->findByResetToken($token);

        if (! $user) {
            return redirect()->to('/auth/forgot-password')
                ->with('error', 'Token reset tidak valid atau sudah kadaluarsa.')
                ->send();
        }

        return $this->view('auth/reset_password', ['token' => $token]);
    }

    public function update(string $token)
    {
        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! $this->userModel->resetPassword($token, $this->request->getPost('password'))) {
            return redirect()->to('/auth/forgot-password')
                ->with('error', 'Token tidak valid atau sudah kadaluarsa.');
        }

        return redirect()->to('/auth/login')
            ->with('success', 'Password berhasil direset. Silakan login.');
    }
}
