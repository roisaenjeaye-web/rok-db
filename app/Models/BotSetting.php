<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting !== null ? $setting->value : $default;
    }

    public static function getBoolean(string $key, bool $default = false): bool
    {
        $setting = static::where('key', $key)->first();
        if ($setting === null) {
            return $default;
        }
        $val = $setting->value;
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
    }
}
