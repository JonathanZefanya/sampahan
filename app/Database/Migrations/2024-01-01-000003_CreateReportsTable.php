<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            // Stored as DECIMAL for maximum portability; use POINT for full MySQL Spatial.
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
            ],
            'photo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'reviewed', 'in_progress', 'cleaned', 'rejected'],
                'default'    => 'pending',
            ],
            'admin_note' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Rejection reason or thank-you note',
            ],
            'is_recurrent_hotspot' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Flagged when location was previously cleaned',
            ],
            'rejection_reason' => [
                'type'       => 'ENUM',
                'constraint' => ['outside_boundary', 'duplicate_active', 'invalid_image', 'manual'],
                'null'       => true,
                'default'    => null,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addKey(['latitude', 'longitude']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reports');
    }

    public function down(): void
    {
        $this->forge->dropTable('reports', true);
    }
}
