<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['key', 'value', 'group', 'description', 'updated_at'];
    protected $useTimestamps = false; // managed manually so we can update `updated_at` only

    // ─── Cache ──────────────────────────────────────────────────────────────
    /** @var array<string,string|null>  In-memory cache for current request */
    private static array $cache = [];

    // ────────────────────────────────────────────────────────────────────────

    /**
     * Return a single setting value by key, with an optional default.
     * Results are cached in-process so the DB is only hit once per request.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (! array_key_exists($key, self::$cache)) {
            $row = $this->where('key', $key)->first();
            self::$cache[$key] = $row ? $row['value'] : null;
        }

        return self::$cache[$key] ?? $default;
    }

    /**
     * Return ALL settings as a flat associative array [key => value].
     * Results are merged into the in-process cache.
     */
    public function getAll(): array
    {
        if (empty(self::$cache)) {
            $rows = $this->findAll();
            foreach ($rows as $row) {
                self::$cache[$row['key']] = $row['value'];
            }
        }

        return self::$cache;
    }

    /**
     * Return settings belonging to a specific group.
     */
    public function getGroup(string $group): array
    {
        $rows   = $this->where('group', $group)->findAll();
        $result = [];
        foreach ($rows as $row) {
            self::$cache[$row['key']] = $row['value'];
            $result[$row['key']]      = $row['value'];
        }

        return $result;
    }

    /**
     * Upsert a setting. Insert if it doesn't exist; update if it does.
     */
    public function setValue(string $key, mixed $value, string $group = 'general'): bool
    {
        $existing = $this->where('key', $key)->first();
        $now      = date('Y-m-d H:i:s');

        if ($existing) {
            $result = $this->where('key', $key)->set([
                'value'      => $value,
                'updated_at' => $now,
            ])->update();
        } else {
            $result = (bool) $this->insert([
                'key'        => $key,
                'value'      => $value,
                'group'      => $group,
                'updated_at' => $now,
            ]);
        }

        // Invalidate in-process cache for this key
        unset(self::$cache[$key]);

        return (bool) $result;
    }

    /**
     * Bulk-save an associative array of [key => value] pairs.
     */
    public function setBulk(array $data, string $group = 'general'): void
    {
        foreach ($data as $key => $value) {
            $this->setValue($key, $value, $group);
        }
    }

    /**
     * Build a CI4-compatible Email config array from `mail` group settings.
     * Consumed by Services\DynamicMailer at runtime – never reads .env.
     */
    public function getMailConfig(): array
    {
        $mail = $this->getGroup('mail');

        return [
            'protocol'  => 'smtp',
            'SMTPHost'  => $mail['smtp_host']      ?? '',
            'SMTPUser'  => $mail['smtp_user']      ?? '',
            'SMTPPass'  => $mail['smtp_pass']      ?? '',
            'SMTPPort'  => (int) ($mail['smtp_port']   ?? 587),
            'SMTPCrypto'=> $mail['smtp_crypto']    ?? 'tls',
            'fromEmail' => $mail['smtp_user']      ?? '',
            'fromName'  => $mail['smtp_from_name'] ?? 'SAMPAHAN',
            'mailType'  => 'html',
            'charset'   => 'UTF-8',
        ];
    }

    /** Flush the in-process cache (useful in tests). */
    public static function flushCache(): void
    {
        self::$cache = [];
    }
}
