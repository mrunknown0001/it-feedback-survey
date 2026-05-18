<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::remember("setting:{$key}", 300, function () use ($key, $default) {
            try {
                if (! Schema::hasTable('settings')) {
                    return $default;
                }
                $row = static::query()->where('key', $key)->first();

                return $row?->value ?? $default;
            } catch (\Throwable) {
                return $default;
            }
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }
}
