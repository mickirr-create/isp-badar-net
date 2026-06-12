<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $table = 'tbl_payment_gateway';

    protected $fillable = [
        'plan_id',
        'routers_id',
        'gateway',
        'gateway_fee',
        'username',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'routers_id');
    }
}
