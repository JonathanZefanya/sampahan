<?php

namespace App\Controllers;

use App\Models\SettingModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController
 *
 * The single place where dynamic app settings are loaded and shared with
 * every view.  All feature controllers must extend this class so that
 * white-label data (app_name, app_logo, app_favicon, city_name, …) is
 * available in every view without per-controller wiring.
 *
 * ── How Global Settings Reach Views ─────────────────────────────────────────
 * 1. initController() loads ALL `settings` rows into $this->appSettings once.
 * 2. render() merges them as `$settings` into every view call automatically.
 * 3. Views use `$settings['app_name']`, `$settings['app_logo']`, etc.
 * 4. The shared layout <head> uses `$settings['app_favicon']` / logo vars
 *    without any per-controller passing.
 * ─────────────────────────────────────────────────────────────────────────────
 */
abstract class BaseController extends Controller
{
    /** CI4 helpers loaded globally for all controllers. */
    protected $helpers = ['url', 'form', 'html', 'text'];

    /**
     * Flat associative array of ALL rows in the `settings` table.
     * Populated once per request in initController().
     *
     * @var array<string, mixed>
     */
    protected array $appSettings = [];

    /**
     * Convenience shortcut to the authenticated user session row.
     */
    protected ?array $authUser = null;

    // ─────────────────────────────────────────────────────────────────────────

    public function initController(
        RequestInterface  $request,
        ResponseInterface $response,
        LoggerInterface   $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Load all settings into memory once per request
        $this->appSettings = (new SettingModel())->getAll();

        // Apply timezone from settings (fallback to Asia/Jakarta)
        $tz = $this->appSettings['app_timezone'] ?? 'Asia/Jakarta';
        if ($tz && in_array($tz, timezone_identifiers_list(), true)) {
            date_default_timezone_set($tz);
        }

        // Shortcut to authenticated user (null for guest pages)
        $this->authUser = session()->get('user');
    }

    // ─── Rendering helper ────────────────────────────────────────────────────

    /**
     * Render a view inside a layout template.
     *
     * Automatically injects:
     *  - `$settings`  → full settings array (app_name, app_logo, favicon, …)
     *  - `$authUser`  → session user row
     *
     * Usage:
     *   return $this->render('layouts/admin', 'admin/dashboard', ['stats' => $stats]);
     */
    protected function render(string $layout, string $view, array $data = []): string
    {
        $data['settings']     = $this->appSettings;
        $data['authUser']     = $this->authUser;
        $data['extraScripts'] = '';
        $data['extraStyle']   = '';
        $data['extraHead']    = '';

        // Build the absolute path to the view file
        $viewFile = APPPATH . 'Views' . DIRECTORY_SEPARATOR
                  . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $view) . '.php';

        // Render the content view in an isolated closure so that
        // variables SET inside the view (e.g. $extraScripts = ob_get_clean())
        // can be captured via references — CI4's view() uses its own isolated
        // scope, so assignments made inside never propagate back.
        $extraScripts = '';
        $extraStyle   = '';
        $extraHead    = '';

        $content = (static function (string $__file, array $__vars,
                                     string &$extraScripts,
                                     string &$extraStyle,
                                     string &$extraHead): string {
            extract($__vars);           // sets $extraScripts/Style/Head = '' + all data vars
            ob_start();
            include $__file;            // view may now reassign $extraScripts etc.
            return ob_get_clean();      // HTML before any ob_start block in the view
        })($viewFile, $data, $extraScripts, $extraStyle, $extraHead);

        return view($layout, array_merge($data, [
            'content'      => $content,
            'extraScripts' => $extraScripts,
            'extraStyle'   => $extraStyle,
            'extraHead'    => $extraHead,
        ]));
    }

    /**
     * Render a bare view (no layout wrapping) – useful for partials / AJAX.
     */
    protected function view(string $view, array $data = []): string
    {
        $data['settings'] = $this->appSettings;
        $data['authUser'] = $this->authUser;

        return view($view, $data);
    }

    // ─── JSON helpers ─────────────────────────────────────────────────────────

    protected function jsonSuccess(mixed $data = null, string $message = 'OK', int $code = 200): ResponseInterface
    {
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ])->setStatusCode($code);
    }

    protected function jsonError(string $message, int $code = 400, mixed $data = null): ResponseInterface
    {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => $message,
            'data'    => $data,
        ])->setStatusCode($code);
    }

    // ─── File upload helper ───────────────────────────────────────────────────

    /**
     * Handle a single file upload to /public/uploads/{subFolder}/.
     *
     * @return array{path: string, url: string, abs: string}|null
     */
    protected function handleUpload(string $fieldName, string $subFolder = ''): ?array
    {
        $file = $this->request->getFile($fieldName);

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon'];
        if (! in_array($file->getMimeType(), $allowed, true)) {
            return null;
        }

        $newName   = $file->getRandomName();
        $uploadDir = FCPATH . 'uploads' . ($subFolder ? '/' . trim($subFolder, '/') : '');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file->move($uploadDir, $newName);

        $relativePath = 'uploads' . ($subFolder ? '/' . trim($subFolder, '/') : '') . '/' . $newName;

        return [
            'path' => $relativePath,
            'url'  => base_url($relativePath),
            'abs'  => $uploadDir . '/' . $newName,
        ];
    }

    // ─── Setting shortcut ─────────────────────────────────────────────────────

    protected function setting(string $key, mixed $default = null): mixed
    {
        return $this->appSettings[$key] ?? $default;
    }
}
