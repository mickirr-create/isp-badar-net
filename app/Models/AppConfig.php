<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    use HasFactory;

    protected $table = 'tbl_appconfig';

    public $timestamps = false;

    protected $fillable = [
        'setting',
        'value',
    ];

    public static function getSetting(string $setting, mixed $default = null): mixed
    {
        $config = static::where('setting', $setting)->first();
        return $config ? $config->value : $default;
    }

    public static function setSetting(string $setting, mixed $value): static
    {
        return static::updateOrCreate(
            ['setting' => $setting],
            ['value' => $value]
        );
    }
}
