<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tbl_transactions';

    protected $fillable = [
        'invoice',
        'username',
        'user_id',
        'plan_name',
        'price',
        'recharged_on',
        'recharged_time',
        'expiration',
        'time',
        'method',
        'routers',
        'type',
        'note',
        'admin_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
