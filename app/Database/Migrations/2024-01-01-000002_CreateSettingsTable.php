<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Settings table using Key-Value store.
 * This drives the entire white-label / multi-tenant behaviour.
 * Never hard-code app identity in .env – use this table.
 */
class CreateSettingsTable extends Migration
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
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'value' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'group' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'general',
                'comment'    => 'general | mail | map | appearance',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('key');
        $this->forge->addKey('group');
        $this->forge->createTable('settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('settings', true);
    }
}
