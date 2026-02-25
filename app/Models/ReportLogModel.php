<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportLogModel extends Model
{
    protected $table         = 'report_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['report_id', 'changed_by', 'old_status', 'new_status', 'note', 'created_at'];
    protected $useTimestamps = false;

    public function log(
        int $reportId,
        ?int $actorId,
        ?string $oldStatus,
        string $newStatus,
        ?string $note = null
    ): int|false {
        return $this->insert([
            'report_id'  => $reportId,
            'changed_by' => $actorId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note'       => $note,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getLogsForReport(int $reportId): array
    {
        return $this->select('report_logs.*, users.name AS actor_name')
                    ->join('users', 'users.id = report_logs.changed_by', 'left')
                    ->where('report_id', $reportId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
}
