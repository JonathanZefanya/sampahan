<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add guest_name & guest_phone to reports, and make user_id nullable
 * so that unauthenticated (guest) users can submit reports.
 */
class AddGuestFieldsToReports extends Migration
{
    public function up(): void
    {
        // Drop the foreign-key constraint on user_id so we can make it nullable
        // (CI4 Forge does not expose dropForeignKey on all drivers, so we use raw SQL)
        $db = \Config\Database::connect();

        // Detect and drop the user_id FK (MySQL only)
        $fks = $db->query(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'reports'
               AND COLUMN_NAME  = 'user_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL"
        )->getResultArray();

        foreach ($fks as $fk) {
            $db->query("ALTER TABLE `reports` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
        }

        // Make user_id nullable
        $this->forge->modifyColumn('reports', [
            'user_id' => [
                'name'       => 'user_id',
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        // Re-add FK with SET NULL on delete so orphaned rows are handled gracefully
        $db->query(
            "ALTER TABLE `reports`
             ADD CONSTRAINT `reports_user_id_foreign`
             FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
             ON DELETE SET NULL ON UPDATE CASCADE"
        );

        // Add guest columns
        $this->forge->addColumn('reports', [
            'guest_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'default'    => null,
                'after'      => 'user_id',
            ],
            'guest_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'guest_name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('reports', 'guest_name');
        $this->forge->dropColumn('reports', 'guest_phone');

        // Restore NOT NULL on user_id  (data loss warning if NULLs exist)
        $this->forge->modifyColumn('reports', [
            'user_id' => [
                'name'       => 'user_id',
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
