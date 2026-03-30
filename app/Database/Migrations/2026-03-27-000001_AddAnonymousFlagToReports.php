<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAnonymousFlagToReports extends Migration
{
    public function up(): void
    {
        $fields = [
            'is_anonymous' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_recurrent_hotspot',
                'comment'    => 'Flag for reporter wish to stay anonymous in Dinas view',
            ],
        ];

        $this->forge->addColumn('reports', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('reports', 'is_anonymous');
    }
}
