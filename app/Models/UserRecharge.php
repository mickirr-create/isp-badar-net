<?php

namespace App\Models;

use Carbon\Carbon;
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
        'throttle_applied',
        'last_throttle_check',
    ];

    protected $casts = [
        'recharged_on' => 'date',
        'expiration' => 'date',
        'throttle_applied' => 'boolean',
        'last_throttle_check' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Scope: active recharges
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'on')
            ->where('expiration', '>=', Carbon::today());
    }

    /**
     * Scope: overdue recharges (not yet throttled)
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'on')
            ->where('expiration', '<', Carbon::today())
            ->where('throttle_applied', false);
    }

    /**
     * Scope: throttled recharges
     */
    public function scopeThrottled($query)
    {
        return $query->where('status', 'on')
            ->where('throttle_applied', true);
    }
}
