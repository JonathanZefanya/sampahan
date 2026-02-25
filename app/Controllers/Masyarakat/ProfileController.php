<?php

namespace App\Controllers\Masyarakat;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    public function index(): string
    {
        $user  = (new UserModel())->find($this->authUser['id']);
        $stats = (new ReportModel())->getStats($this->authUser['id']);
        return $this->render('layouts/masyarakat', 'masyarakat/profile', ['user' => $user, 'stats' => $stats]);
    }

    public function update()
    {
        $id      = (int) $this->authUser['id'];
        $update  = $this->request->getPost('_update') ?? 'info';
        $model   = new UserModel();
        $current = $model->find($id);

        if ($update === 'info') {
            $rules = [
                'name'  => 'required|min_length[3]',
                'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            ];
            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
            $data = [
                'name'  => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
            ];
            $model->update($id, $data);
            session()->set('user', array_merge(session()->get('user') ?? [], $data));
            return redirect()->to('/masyarakat/profile')->with('success', 'Profil diperbarui.');
        }

        if ($update === 'password') {
            $currentPwd = $this->request->getPost('current_password') ?? '';
            $newPwd     = $this->request->getPost('password') ?? '';
            $confirm    = $this->request->getPost('password_confirm') ?? '';
            if (! password_verify($currentPwd, $current['password'] ?? '')) {
                return redirect()->back()->with('error', 'Password saat ini salah.');
            }
            if (strlen($newPwd) < 8) {
                return redirect()->back()->with('error', 'Password baru minimal 8 karakter.');
            }
            if ($newPwd !== $confirm) {
                return redirect()->back()->with('error', 'Konfirmasi password tidak cocok.');
            }
            $model->update($id, ['password' => password_hash($newPwd, PASSWORD_BCRYPT)]);
            return redirect()->to('/masyarakat/profile')->with('success', 'Password berhasil diubah.');
        }

        return redirect()->to('/masyarakat/profile');
    }
}
