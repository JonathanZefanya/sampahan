<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingModel;

/**
 * SettingsController
 *
 * Full CRUD for the `settings` table via a single admin GUI.
 * This is what makes SAMPAHAN 100% white-label: every piece of identity
 * (name, logo, favicon, city, map centre, SMTP, GeoJSON) is changed here.
 */
class SettingsController extends BaseController
{
    private SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index(): string
    {
        // Group settings for organised tabs in the view
        $data = [
            'appearance' => $this->settingModel->getGroup('appearance'),
            'map'        => $this->settingModel->getGroup('map'),
            'mail'       => $this->settingModel->getGroup('mail'),
            'general'    => $this->settingModel->getGroup('general'),
        ];

        return $this->render('layouts/admin', 'admin/settings', $data);
    }

    /**
     * Save non-file settings (text fields, selects, toggles) in bulk.
     */
    public function save()
    {
        // Whitelist of plain-text keys we accept via this form POST
        $textKeys = [
            // Appearance
            'app_name', 'city_name',
            // Map
            'map_center_lat', 'map_center_long', 'map_default_zoom',
            // Mail
            'smtp_host', 'smtp_user', 'smtp_pass', 'smtp_port', 'smtp_crypto', 'smtp_from_name',
            // General
            'enable_email_verification', 'duplicate_radius_meters', 'app_timezone',
        ];

        foreach ($textKeys as $key) {
            $value = $this->request->getPost($key);
            if ($value !== null) {
                $this->settingModel->setValue($key, $value);
            }
        }

        $tab = $this->request->getPost('_tab') ?? '';
        $hash = in_array($tab, ['#tab-appearance','#tab-map','#tab-mail','#tab-general'], true) ? $tab : '';

        return redirect()->to('/admin/settings' . $hash)->with('success', 'Pengaturan disimpan.');
    }

    /**
     * Upload & replace App Logo.
     */
    public function uploadLogo()
    {
        $upload = $this->handleUpload('app_logo', 'brand');

        if (! $upload) {
            return redirect()->to('/admin/settings#tab-appearance')->with('error', 'Upload logo gagal. Pastikan file adalah gambar valid.');
        }

        // Delete old logo file if different from default
        $oldPath = $this->settingModel->get('app_logo');
        $this->deleteOldFile($oldPath, ['uploads/logo.png']);

        $this->settingModel->setValue('app_logo', $upload['path'], 'appearance');

        return redirect()->to('/admin/settings#tab-appearance')->with('success', 'Logo berhasil diperbarui.');
    }

    /**
     * Upload & replace Favicon.
     */
    public function uploadFavicon()
    {
        $upload = $this->handleUpload('app_favicon', 'brand');

        if (! $upload) {
            return redirect()->to('/admin/settings#tab-appearance')->with('error', 'Upload favicon gagal.');
        }

        $oldPath = $this->settingModel->get('app_favicon');
        $this->deleteOldFile($oldPath, ['uploads/favicon.ico']);

        $this->settingModel->setValue('app_favicon', $upload['path'], 'appearance');

        return redirect()->to('/admin/settings#tab-appearance')->with('success', 'Favicon berhasil diperbarui.');
    }

    /**
     * Upload & store raw GeoJSON for city boundary.
     * Accepts a *.geojson or *.json text file OR pasted text via textarea.
     */
    public function uploadGeoJson()
    {
        $pastedJson = $this->request->getPost('city_boundary_geojson');

        // Option A: JSON pasted in textarea
        if (! empty($pastedJson)) {
            $decoded = json_decode($pastedJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->to('/admin/settings#map')
                    ->with('error', 'GeoJSON tidak valid: ' . json_last_error_msg());
            }

            $this->settingModel->setValue('city_boundary_geojson', $pastedJson, 'map');
            return redirect()->to('/admin/settings#tab-map')->with('success', 'Batas wilayah (GeoJSON) disimpan.');
        }

        // Option B: File upload
        $file = $this->request->getFile('geojson_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to('/admin/settings')->with('error', 'Tidak ada file GeoJSON yang diunggah.');
        }

        $content = file_get_contents($file->getTempName());
        $decoded = json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->to('/admin/settings')
                ->with('error', 'File bukan GeoJSON valid: ' . json_last_error_msg());
        }

        $this->settingModel->setValue('city_boundary_geojson', $content, 'map');
        return redirect()->to('/admin/settings#tab-map')->with('success', 'Batas wilayah (GeoJSON) berhasil dimuat dari file.');
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function deleteOldFile(?string $relativePath, array $keepDefaults = []): void
    {
        if (! $relativePath || in_array($relativePath, $keepDefaults, true)) {
            return;
        }

        $abs = FCPATH . $relativePath;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
