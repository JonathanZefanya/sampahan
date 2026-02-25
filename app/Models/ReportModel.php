<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\GeoHelper;

/**
 * ReportModel
 *
 * Core intelligence for spatial validation lives here:
 *   1. Point-in-Polygon  → boundary check against city GeoJSON
 *   2. Duplicate Radius  → < N metres away + active report check
 *   3. Status transitions with log emission
 */
class ReportModel extends Model
{
    protected $table         = 'reports';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'user_id', 'latitude', 'longitude', 'photo_path',
        'description', 'status', 'admin_note',
        'is_recurrent_hotspot', 'rejection_reason',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Status constants – single source of truth
    const STATUS_PENDING     = 'pending';
    const STATUS_REVIEWED    = 'reviewed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_CLEANED     = 'cleaned';
    const STATUS_REJECTED    = 'rejected';

    // Allowed forward transitions
    const TRANSITIONS = [
        self::STATUS_PENDING     => self::STATUS_REVIEWED,
        self::STATUS_REVIEWED    => self::STATUS_IN_PROGRESS,
        self::STATUS_IN_PROGRESS => self::STATUS_CLEANED,
    ];

    // ─── Spatial Validation ──────────────────────────────────────────────────

    /**
     * Check whether ($lat, $lng) lies inside the stored city boundary.
     *
     * @param  string $cityGeoJson  Raw GeoJSON string from `settings`.
     * @return bool
     */
    public function isInsideBoundary(float $lat, float $lng, string $cityGeoJson): bool
    {
        if (empty(trim($cityGeoJson))) {
            // No boundary configured → treat as valid (open mode)
            return true;
        }

        $geo = json_decode($cityGeoJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($geo)) {
            return true; // Malformed JSON → fail-open
        }

        // Support FeatureCollection, Feature, or bare Polygon/MultiPolygon
        $geometry = $this->extractGeometry($geo);
        if (! $geometry) {
            return true;
        }

        return GeoHelper::pointInPolygon($lat, $lng, $geometry);
    }

    /**
     * Duplicate check within a configurable radius (default 10 m).
     *
     * Returns an array with:
     *   ['isDuplicate' => bool, 'scenario' => 'active'|'cleaned'|null, 'report' => row|null]
     */
    public function checkDuplicate(float $lat, float $lng, float $radiusMeters = 10.0): array
    {
        // Fetch all non-rejected reports – we do haversine in PHP (simple, avoids
        // stored-function dependency). For very high volumes, move to a MySQL
        // ST_Distance_Sphere call.
        $candidates = $this->whereNotIn('status', [self::STATUS_REJECTED])
                           ->findAll();

        foreach ($candidates as $report) {
            $dist = GeoHelper::haversineDistance(
                $lat, $lng,
                (float) $report['latitude'],
                (float) $report['longitude']
            );

            if ($dist <= $radiusMeters) {
                if ($report['status'] === self::STATUS_CLEANED) {
                    return ['isDuplicate' => false, 'scenario' => 'cleaned', 'report' => $report];
                }

                // Pending / Reviewed / In-Progress → reject as duplicate
                return ['isDuplicate' => true, 'scenario' => 'active', 'report' => $report];
            }
        }

        return ['isDuplicate' => false, 'scenario' => null, 'report' => null];
    }

    // ─── Status Management ───────────────────────────────────────────────────

    /**
     * Advance a report to the next allowed status.
     * Emits a ReportLog entry. Returns false if transition is illegal.
     */
    public function advanceStatus(int $reportId, int $actorId, ?string $note = null): bool
    {
        $report = $this->find($reportId);
        if (! $report) {
            return false;
        }

        $nextStatus = self::TRANSITIONS[$report['status']] ?? null;
        if (! $nextStatus) {
            return false;
        }

        $this->db->transStart();
        $this->update($reportId, ['status' => $nextStatus, 'admin_note' => $note]);

        $logModel = new ReportLogModel();
        $logModel->log($reportId, $actorId, $report['status'], $nextStatus, $note);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Reject a report with a machine-readable reason.
     */
    public function reject(int $reportId, string $reason, string $note = '', ?int $actorId = null): bool
    {
        $report = $this->find($reportId);
        if (! $report) {
            return false;
        }

        $this->db->transStart();
        $this->update($reportId, [
            'status'           => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'admin_note'       => $note,
        ]);

        $logModel = new ReportLogModel();
        $logModel->log($reportId, $actorId, $report['status'], self::STATUS_REJECTED, $note);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    // ─── Statistics ──────────────────────────────────────────────────────────

    public function getStats(?int $userId = null): array
    {
        $builder = $userId ? $this->where('user_id', $userId) : $this;

        return [
            'total'       => (clone $builder)->countAllResults(),
            'pending'     => (clone $builder)->where('status', self::STATUS_PENDING)->countAllResults(),
            'reviewed'    => (clone $builder)->where('status', self::STATUS_REVIEWED)->countAllResults(),
            'in_progress' => (clone $builder)->where('status', self::STATUS_IN_PROGRESS)->countAllResults(),
            'cleaned'     => (clone $builder)->where('status', self::STATUS_CLEANED)->countAllResults(),
            'rejected'    => (clone $builder)->where('status', self::STATUS_REJECTED)->countAllResults(),
            'hotspots'    => (clone $builder)->where('is_recurrent_hotspot', 1)->countAllResults(),
        ];
    }

    /**
     * Return all active (non-rejected, non-cleaned) reports as lightweight GeoJSON
     * FeatureCollection for Leaflet rendering.
     */
    public function toGeoJson(?string $status = null): array
    {
        // `pending` and `rejected` reports are never shown on the public map.
        $hidden = [self::STATUS_PENDING, self::STATUS_REJECTED];

        $q = $this->select('reports.id, reports.latitude, reports.longitude,
                            reports.status, reports.description,
                            reports.created_at, reports.is_recurrent_hotspot,
                            users.name AS reporter_name')
                  ->join('users', 'users.id = reports.user_id', 'left')
                  ->whereNotIn('reports.status', $hidden);

        if ($status && ! in_array($status, $hidden, true)) {
            $q = $q->where('reports.status', $status);
        }

        $rows     = $q->findAll();
        $features = [];

        foreach ($rows as $row) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type'        => 'Point',
                    'coordinates' => [(float) $row['longitude'], (float) $row['latitude']],
                ],
                'properties' => [
                    'id'           => $row['id'],
                    'status'       => $row['status'],
                    'description'  => $row['description'],
                    'reporter'     => $row['reporter_name'],
                    'created_at'   => $row['created_at'],
                    'is_hotspot'   => (bool) $row['is_recurrent_hotspot'],
                ],
            ];
        }

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function extractGeometry(array $geo): ?array
    {
        if (isset($geo['type'])) {
            if ($geo['type'] === 'FeatureCollection' && ! empty($geo['features'])) {
                return $geo['features'][0]['geometry'] ?? null;
            }
            if ($geo['type'] === 'Feature') {
                return $geo['geometry'] ?? null;
            }
            if (in_array($geo['type'], ['Polygon', 'MultiPolygon'], true)) {
                return $geo;
            }
        }

        return null;
    }
}
