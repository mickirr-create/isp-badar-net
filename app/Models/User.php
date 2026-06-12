<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

    protected $table = 'tbl_users';

    protected $fillable = [
        'root',
        'photo',
        'username',
        'fullname',
        'password',
        'phone',
        'email',
        'city',
        'subdistrict',
        'ward',
        'user_type',
        'status',
        'data',
        'last_login',
        'login_token',
        'created_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'last_login' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'userid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'admin_id');
    }
}
