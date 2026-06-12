<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'tbl_customers';

    protected $fillable = [
        'username',
        'password',
        'name',
        'email',
        'phone',
        'address',
        'balance',
        'auto_renewal',
        'last_login',
        'last_login_ip',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'balance' => 'float',
        'auto_renewal' => 'boolean',
        'created_at' => 'datetime',
        'last_login' => 'datetime',
    ];

    public function recharges(): HasMany
    {
        return $this->hasMany(UserRecharge::class, 'customer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CustomerField::class, 'customer_id');
    }

    public function inboxMessages(): HasMany
    {
        return $this->hasMany(CustomerInbox::class, 'customer_id');
    }

    public function plans(): HasManyThrough
    {
        return $this->HasManyThrough(Plan::class, UserRecharge::class, 'customer_id', 'id', 'id', 'plan_id');
    }
}
