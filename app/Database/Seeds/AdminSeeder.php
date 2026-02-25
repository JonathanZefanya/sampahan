<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'name'              => 'Super Admin',
            'email'             => 'admin@sampahan.id',
            'password'          => password_hash('Admin@1234', PASSWORD_BCRYPT),
            'role'              => 'admin',
            'is_active'         => 1,
            'email_verified_at' => $now,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    }
}
