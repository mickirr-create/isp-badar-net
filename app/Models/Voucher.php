<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'tbl_voucher';

    protected $fillable = [
        'type',
        'routers',
        'id_plan',
        'code',
        'user',
        'status',
        'used_date',
        'generated_by',
    ];

    protected $casts = [
        'used_date' => 'datetime nullable',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan');
    }
}
