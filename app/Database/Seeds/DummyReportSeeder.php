<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DummyReportSeeder
 *
 * Inserts ~35 realistic dummy waste reports spread around
 * Kota Tangerang Selatan (centre ~-6.302 / 106.707).
 *
 * Usage:
 *   php spark db:seed DummyReportSeeder
 */
class DummyReportSeeder extends Seeder
{
    // ── Configurable centre (Kota Tangerang Selatan) ─────────────────────────
    private float $centerLat = -6.3021;
    private float $centerLng = 106.7074;
    private float $spreadDeg = 0.048; // fits inside Tangsel boundary

    public function run(): void
    {
        // ── Resolve user IDs ──────────────────────────────────────────────────
        $dinasRow  = $this->db->table('users')->where('email', 'dinas@sampahan.id')->get()->getRowArray();
        $masyRow   = $this->db->table('users')->where('email', 'masyarakat@sampahan.id')->get()->getRowArray();

        if (! $dinasRow || ! $masyRow) {
            echo "  ERROR: Make sure both dinas@sampahan.id and masyarakat@sampahan.id accounts exist.\n";
            return;
        }

        $dinasId = (int) $dinasRow['id'];
        $masyId  = (int) $masyRow['id'];

        // ── Report templates ─────────────────────────────────────────────────
        $descriptions = [
            'Tumpukan sampah rumah tangga di pinggir jalan, sudah berbau dan mengganggu warga sekitar.',
            'Sampah plastik berserakan di dekat saluran air, dikhawatirkan menyumbat drainase.',
            'Pembuangan sampah liar di lahan kosong, terdapat sampah elektronik dan kasur bekas.',
            'Sampah menumpuk di depan gang, sudah beberapa hari tidak diangkut.',
            'Tong sampah komunal penuh dan meluber ke trotoar.',
            'Sampah sisa bangunan dibuang sembarangan di bahu jalan.',
            'Tumpukan kardus dan styrofoam dibakar warga, asap mengganggu pemukiman.',
            'Sampah organik membusuk di sudut pasar tradisional.',
            'Kantong plastik berisi sampah dibuang di bawah jembatan.',
            'Puing-puing bongkaran rumah dibuang di pinggir sungai kecil.',
            'Sampah campur berserakan di area taman bermain anak-anak.',
            'Botol-botol plastik bertebaran di sepanjang trotoar pasar.',
            'Limbah cucian motor dibuang ke got, air jadi keruh dan berbau oli.',
            'Sampah sisa hajatan tidak dibersihkan, sudah 2 hari di pinggir jalan.',
            'Pembuangan ilegal berupa ban bekas dan oli di lahan kosong dekat sekolah.',
            'Tumpukan sampah di dekat mushola, menyebabkan bau tidak sedap saat ibadah.',
            'Sampah plastik mengapung di saluran irigasi.',
            'Kotak-kotak kardus besar dan aneka sampah di depan ruko tutup.',
            'Sampah bertumpuk di bawah jembatan penyeberangan orang.',
            'Sisa makanan kios pinggir jalan berserakan, mengundang tikus dan lalat.',
            'Kantong kresek hitam berisi sampah ditinggal di jalur hijau taman kota.',
            'Sampah popok sekali pakai berserakan di got depan perumahan.',
            'Tumpukan sampah halaman rumah kosong sudah menutup sebagian jalan.',
            'Sampah konstruksi berupa semen dan batu bata berserakan di jalan.',
            'Bangkai sofa dan lemari dibuang di pinggir jalan raya.',
        ];

        $adminNotes = [
            'cleaned'     => [
                'Sampah telah diangkut oleh petugas kebersihan. Terima kasih atas laporannya!',
                'Lokasi sudah dibersihkan. Warga diimbau tidak membuang sampah sembarangan.',
                'Tim dinas telah menangani. Area sudah bersih kembali.',
            ],
            'rejected'    => [
                'Setelah pengecekan lapangan, tidak ditemukan tumpukan sampah di koordinat yang dilaporkan.',
                'Foto tidak sesuai dengan lokasi koordinat yang dikirimkan. Mohon pastikan GPS aktif.',
                'Laporan duplikat — sudah ditangani melalui laporan sebelumnya.',
            ],
            'in_progress' => [
                'Jadwal pengangkutan sudah dimasukkan untuk minggu ini.',
                'Petugas sudah ditugaskan ke lokasi.',
            ],
        ];

        // ── Build report rows ─────────────────────────────────────────────────
        $baseDate = strtotime('2025-11-01');
        $now      = time();
        $reports  = [];
        $seed     = 42; // deterministic "random"

        // Status distribution: 8 pending, 5 reviewed, 8 in_progress, 10 cleaned, 4 rejected
        $plan = [
            ['status' => 'pending',     'count' => 8,  'hotspot' => false],
            ['status' => 'reviewed',    'count' => 5,  'hotspot' => false],
            ['status' => 'in_progress', 'count' => 8,  'hotspot' => false],
            ['status' => 'cleaned',     'count' => 8,  'hotspot' => false],
            ['status' => 'cleaned',     'count' => 2,  'hotspot' => true],
            ['status' => 'rejected',    'count' => 4,  'hotspot' => false],
        ];

        $descIdx = 0;
        foreach ($plan as $group) {
            for ($i = 0; $i < $group['count']; $i++) {
                // LCG-style pseudo-random
                $seed = ($seed * 1664525 + 1013904223) & 0x7FFFFFFF;
                $latOffset = (($seed % 10000) / 10000 - 0.5) * 2 * $this->spreadDeg;

                $seed = ($seed * 1664525 + 1013904223) & 0x7FFFFFFF;
                $lngOffset = (($seed % 10000) / 10000 - 0.5) * 2 * $this->spreadDeg;

                $seed = ($seed * 1664525 + 1013904223) & 0x7FFFFFFF;
                $tsOffset  = ($seed % ($now - $baseDate));
                $createdTs = $baseDate + $tsOffset;

                $reports[] = [
                    'status'               => $group['status'],
                    'hotspot'              => $group['hotspot'],
                    'lat'                  => round($this->centerLat + $latOffset, 7),
                    'lng'                  => round($this->centerLng + $lngOffset, 7),
                    'description'          => $descriptions[$descIdx % count($descriptions)],
                    'created_at'           => date('Y-m-d H:i:s', $createdTs),
                    'updated_at'           => date('Y-m-d H:i:s', min($createdTs + 86400 * 3, $now)),
                ];
                $descIdx++;
            }
        }

        // Shuffle deterministically by sorting on created_at
        usort($reports, fn($a, $b) => strcmp($a['created_at'], $b['created_at']));

        // ── Insert reports + logs ─────────────────────────────────────────────
        $reportsTable = $this->db->table('reports');
        $logsTable    = $this->db->table('report_logs');
        $inserted     = 0;

        foreach ($reports as $r) {
            $adminNote = null;
            if ($r['status'] === 'cleaned') {
                $seed      = ($seed * 1664525 + 1013904223) & 0x7FFFFFFF;
                $adminNote = $adminNotes['cleaned'][$seed % count($adminNotes['cleaned'])];
            } elseif ($r['status'] === 'rejected') {
                $seed      = ($seed * 1664525 + 1013904223) & 0x7FFFFFFF;
                $adminNote = $adminNotes['rejected'][$seed % count($adminNotes['rejected'])];
            } elseif ($r['status'] === 'in_progress') {
                $seed      = ($seed * 1664525 + 1013904223) & 0x7FFFFFFF;
                $adminNote = $adminNotes['in_progress'][$seed % count($adminNotes['in_progress'])];
            }

            $reportsTable->insert([
                'user_id'              => $masyId,
                'latitude'             => $r['lat'],
                'longitude'            => $r['lng'],
                'photo_path'           => null,
                'description'          => $r['description'],
                'status'               => $r['status'],
                'admin_note'           => $adminNote,
                'is_recurrent_hotspot' => $r['hotspot'] ? 1 : 0,
                'rejection_reason'     => $r['status'] === 'rejected' ? 'manual' : null,
                'created_at'           => $r['created_at'],
                'updated_at'           => $r['updated_at'],
            ]);
            $reportId = $this->db->insertID();

            // Build status transition logs
            $logTs = strtotime($r['created_at']);
            $logsTable->insert([
                'report_id'  => $reportId,
                'changed_by' => $masyId,
                'old_status' => null,
                'new_status' => 'pending',
                'note'       => null,
                'created_at' => date('Y-m-d H:i:s', $logTs),
            ]);

            $transitions = match ($r['status']) {
                'reviewed'    => ['pending'     => 'reviewed'],
                'in_progress' => ['pending'     => 'reviewed', 'reviewed'    => 'in_progress'],
                'cleaned'     => ['pending'     => 'reviewed', 'reviewed'    => 'in_progress', 'in_progress' => 'cleaned'],
                'rejected'    => ['pending'     => 'rejected'],
                default       => [],
            };

            foreach ($transitions as $old => $new) {
                $logTs += mt_rand(3600, 86400);
                $logsTable->insert([
                    'report_id'  => $reportId,
                    'changed_by' => $dinasId,
                    'old_status' => $old,
                    'new_status' => $new,
                    'note'       => ($new === 'cleaned' || $new === 'rejected') ? $adminNote : null,
                    'created_at' => date('Y-m-d H:i:s', min($logTs, $now)),
                ]);
            }

            $inserted++;
        }

        echo "  ✓ Inserted {$inserted} dummy reports with status logs.\n";
        echo "  ✓ Assigned to masyarakat@sampahan.id (id={$masyId}).\n";
        echo "  ✓ Logs attributed to dinas@sampahan.id (id={$dinasId}) for status changes.\n";
    }
}
