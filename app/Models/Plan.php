<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tbl_plans';

    protected $fillable = [
        'name_plan',
        'id_bw',
        'price',
        'price_format',
        'description',
        'validity',
        'validity_unit',
        'enabled',
        'is_radius',
        'pool',
        'type',
        'throttle_profile',
        'created_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'is_radius' => 'boolean',
    ];

    public function bandwidth(): BelongsTo
    {
        return $this->belongsTo(Bandwidth::class, 'id_bw');
    }

    public function recharges(): HasMany
    {
        return $this->hasMany(UserRecharge::class, 'plan_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'id_plan');
    }
}
