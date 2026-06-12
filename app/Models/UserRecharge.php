<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRecharge extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tbl_user_recharges';

    protected $fillable = [
        'customer_id',
        'username',
        'plan_id',
        'namebp',
        'recharged_on',
        'recharged_time',
        'expiration',
        'time',
        'status',
        'method',
        'routers',
        'type',
        'admin_id',
    ];

    protected $casts = [
        'recharged_on' => 'date',
        'expiration' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
