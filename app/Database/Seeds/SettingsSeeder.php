<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeds the `settings` table with sane defaults for Tangsel.
 * Because every key is soft-configurable via the Admin GUI, merely
 * changing the values through the UI is enough to white-label for
 * a different city – no code change required.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $settings = [
            // ── Appearance ──────────────────────────────────────────────────
            ['key' => 'app_name',    'value' => 'SAMPAHAN',          'group' => 'appearance', 'description' => 'Application display name'],
            ['key' => 'app_logo',    'value' => 'uploads/logo.png',  'group' => 'appearance', 'description' => 'Path to app logo (stored in /public)'],
            ['key' => 'app_favicon', 'value' => 'uploads/favicon.ico','group' => 'appearance', 'description' => 'Path to favicon'],
            ['key' => 'city_name',   'value' => 'Kota Tangerang Selatan', 'group' => 'appearance', 'description' => 'City display name used across the UI'],

            // ── Map ──────────────────────────────────────────────────────────
            ['key' => 'map_center_lat',           'value' => '-6.2884',  'group' => 'map', 'description' => 'Default map center latitude'],
            ['key' => 'map_center_long',          'value' => '106.7135', 'group' => 'map', 'description' => 'Default map center longitude'],
            ['key' => 'map_default_zoom',         'value' => '12',       'group' => 'map', 'description' => 'Default Leaflet zoom level'],
            ['key' => 'city_boundary_geojson',    'value' => null,        'group' => 'map', 'description' => 'GeoJSON polygon for city boundary (used for masking & validation)'],

            // ── Mail ─────────────────────────────────────────────────────────
            ['key' => 'smtp_host',     'value' => 'smtp.gmail.com', 'group' => 'mail', 'description' => 'SMTP host'],
            ['key' => 'smtp_user',     'value' => '',               'group' => 'mail', 'description' => 'SMTP username / sender email'],
            ['key' => 'smtp_pass',     'value' => '',               'group' => 'mail', 'description' => 'SMTP password (stored encrypted recommended)'],
            ['key' => 'smtp_port',     'value' => '587',            'group' => 'mail', 'description' => 'SMTP port'],
            ['key' => 'smtp_crypto',   'value' => 'tls',            'group' => 'mail', 'description' => 'tls | ssl | none'],
            ['key' => 'smtp_from_name','value' => 'SAMPAHAN System','group' => 'mail', 'description' => 'From name in outgoing emails'],

            // ── General ──────────────────────────────────────────────────────
            ['key' => 'enable_email_verification', 'value' => '0', 'group' => 'general', 'description' => '1 = require email verification on register, 0 = auto-activate'],
            ['key' => 'duplicate_radius_meters',   'value' => '10','group' => 'general', 'description' => 'Radius (m) to consider a report a duplicate'],
        ];

        foreach ($settings as &$row) {
            $row['updated_at'] = $now;
        }
        unset($row);

        $this->db->table('settings')->insertBatch($settings);
    }
}
