<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tbl_routers';

    protected $fillable = [
        'name',
        'ip_address',
        'username',
        'password',
        'community',
        'description',
        'enabled',
        'type',
        'status',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function recharges(): HasMany
    {
        return $this->hasMany(UserRecharge::class, 'routers_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'routers_id');
    }

    public function pools(): HasMany
    {
        return $this->hasMany(Pool::class, 'routers');
    }
}
