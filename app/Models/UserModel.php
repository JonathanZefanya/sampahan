<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'name', 'email', 'password', 'role',
        'is_active', 'activation_token', 'email_verified_at',
        'reset_token', 'reset_token_expires_at',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';

    // Hide sensitive columns from toArray / JSON
    protected $hiddenFields = ['password', 'activation_token', 'reset_token'];

    // ─── Auth helpers ────────────────────────────────────────────────────────

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Validate credentials.
     * Returns the user row (with password) or null on failure.
     */
    public function attemptLogin(string $email, string $password): ?array
    {
        $user = $this->where('email', $email)->first();
        if (! $user || ! password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }

    // ─── Activation ──────────────────────────────────────────────────────────

    public function generateActivationToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update($userId, ['activation_token' => $token]);
        return $token;
    }

    public function activateByToken(string $token): bool
    {
        $user = $this->where('activation_token', $token)->first();
        if (! $user) {
            return false;
        }

        $this->update($user['id'], [
            'is_active'          => 1,
            'email_verified_at'  => date('Y-m-d H:i:s'),
            'activation_token'   => null,
        ]);

        return true;
    }

    // ─── Password Reset ───────────────────────────────────────────────────────

    public function generateResetToken(string $email): ?string
    {
        $user = $this->findByEmail($email);
        if (! $user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $this->update($user['id'], [
            'reset_token'            => $token,
            'reset_token_expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ]);

        return $token;
    }

    public function findByResetToken(string $token): ?array
    {
        return $this->where('reset_token', $token)
                    ->where('reset_token_expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->findByResetToken($token);
        if (! $user) {
            return false;
        }

        $this->update($user['id'], [
            'password'               => password_hash($newPassword, PASSWORD_BCRYPT),
            'reset_token'            => null,
            'reset_token_expires_at' => null,
        ]);

        return true;
    }

    // ─── Admin helpers ────────────────────────────────────────────────────────

    public function deactivate(int $userId): bool
    {
        return (bool) $this->update($userId, ['is_active' => 0]);
    }

    public function activate(int $userId): bool
    {
        return (bool) $this->update($userId, ['is_active' => 1]);
    }

    public function getUsersByRole(string $role): array
    {
        return $this->where('role', $role)->findAll();
    }

    public function getStats(): array
    {
        return [
            'total'       => $this->countAllResults(),
            'admin'       => $this->where('role', 'admin')->countAllResults(),
            'dinas'       => $this->where('role', 'dinas')->countAllResults(),
            'masyarakat'  => $this->where('role', 'masyarakat')->countAllResults(),
            'inactive'    => $this->where('is_active', 0)->countAllResults(),
        ];
    }
}
