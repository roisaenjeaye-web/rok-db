<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * In-memory cache for all settings to prevent N+1 queries.
     *
     * @var array<string, mixed>|null
     */
    protected static ?array $memo = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::$memo = null;
        });

        static::deleted(function () {
            static::$memo = null;
        });
    }

    /**
     * Warm up all settings into memory with a single query.
     */
    public static function warm(): void
    {
        if (static::$memo === null) {
            try {
                static::$memo = static::pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                static::$memo = [];
            }
        }
    }

    /**
     * Clear in-memory cache.
     */
    public static function clearCache(): void
    {
        static::$memo = null;
    }

    public static function get(string $key, $default = null)
    {
        static::warm();

        if (array_key_exists($key, static::$memo)) {
            return static::$memo[$key];
        }

        return $default;
    }

    public static function getBoolean(string $key, bool $default = false): bool
    {
        $val = static::get($key, null);
        if ($val === null) {
            return $default;
        }
        if (is_bool($val)) {
            return $val;
        }
        $val = strtolower(trim((string) $val));
        if ($val === '1' || $val === 'true' || $val === 'on' || $val === 'yes') {
            return true;
        }
        if ($val === '0' || $val === 'false' || $val === 'off' || $val === 'no' || $val === '') {
            return false;
        }
        return $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        if (static::$memo !== null) {
            static::$memo[$key] = $value;
        }
    }
}
