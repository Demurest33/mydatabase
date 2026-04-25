<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppSetting extends Model
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = DB::table('app_settings')->where('key', $key)->first();
        return $row?->value ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $now = now();
        $exists = DB::table('app_settings')->where('key', $key)->exists();
        if ($exists) {
            DB::table('app_settings')->where('key', $key)
                ->update(['value' => $value, 'updated_at' => $now]);
        } else {
            DB::table('app_settings')
                ->insert(['key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
