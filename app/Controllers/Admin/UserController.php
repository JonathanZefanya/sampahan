<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index(): string
    {
        $role  = $this->request->getGet('role') ?? '';
        $users = $role
            ? $this->userModel->where('role', $role)->findAll()
            : $this->userModel->findAll();

        return $this->render('layouts/admin', 'admin/users/index', [
            'users'       => $users,
            'filterRole'  => $role,
        ]);
    }

    public function create(): string
    {
        return $this->render('layouts/admin', 'admin/users/form', ['user' => null]);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[3]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[admin,dinas,masyarakat]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->insert([
            'name'              => $this->request->getPost('name'),
            'email'             => $this->request->getPost('email'),
            'password'          => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'              => $this->request->getPost('role'),
            'is_active'         => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(int $id): string
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        return $this->render('layouts/admin', 'admin/users/form', ['user' => $user]);
    }

    public function update(int $id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'name'  => 'required|min_length[3]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role'  => 'required|in_list[admin,dinas,masyarakat]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role'  => $this->request->getPost('role'),
        ];

        $newPassword = $this->request->getPost('password');
        if (! empty($newPassword)) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $updateData);

        return redirect()->to('/admin/users')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Toggle active / banned status.
     * Never physically deletes to preserve report history.
     */
    public function toggle(int $id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            return $this->jsonError('User tidak ditemukan.', 404);
        }

        // Prevent admin from banning themselves
        if ($id === (int) ($this->authUser['id'] ?? 0)) {
            return $this->jsonError('Tidak dapat menonaktifkan akun sendiri.');
        }

        if ($user['is_active']) {
            $this->userModel->deactivate($id);
            $msg = 'Akun dinonaktifkan.';
        } else {
            $this->userModel->activate($id);
            $msg = 'Akun diaktifkan kembali.';
        }

        return $this->jsonSuccess(['is_active' => ! $user['is_active']], $msg);
    }

    /**
     * Impersonate a user by temporarily switching their session.
     * Stores original admin session for later restoration.
     */
    public function impersonate(int $id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        if (! $user['is_active']) {
            return redirect()->to('/admin/users')->with('error', 'Tidak bisa impersonate user yang nonaktif.');
        }

        // Store original admin session
        session()->set('impersonation_original', [
            'id'        => $this->authUser['id'],
            'name'      => $this->authUser['name'],
            'email'     => $this->authUser['email'],
            'role'      => $this->authUser['role'],
            'is_active' => $this->authUser['is_active'],
        ]);

        session()->set('is_impersonating', true);

        // Switch to impersonated user session
        session()->set('user', [
            'id'        => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'is_active' => $user['is_active'],
        ]);

        $dashUrl = $this->getDashboardByRole($user['role']);
        return redirect()->to($dashUrl)->with('info', "Anda sekarang login sebagai {$user['name']}");
    }

    /**
     * Exit impersonation and restore original admin session.
     */
    public function exitImpersonation()
    {
        $original = session()->get('impersonation_original');
        if (! $original) {
            return redirect()->to('/admin/dashboard')->with('error', 'Tidak ada session impersonation aktif.');
        }

        // Restore original admin session
        session()->set('user', $original);
        session()->remove('impersonation_original');
        session()->remove('is_impersonating');

        return redirect()->to('/admin/dashboard')->with('success', "Anda telah kembali ke akun admin.");
    }

    /**
     * Helper to get dashboard URL by role
     */
    private function getDashboardByRole(string $role): string
    {
        return match ($role) {
            'admin'      => '/admin/dashboard',
            'dinas'      => '/dinas/dashboard',
            'masyarakat' => '/masyarakat/dashboard',
            default      => '/masyarakat/dashboard',
        };
    }
}
