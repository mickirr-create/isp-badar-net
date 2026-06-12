<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerInbox extends Model
{
    use HasFactory;

    protected $table = 'tbl_customers_inbox';

    protected $fillable = [
        'customer_id',
        'subject',
        'message',
        'from',
        'status',
        'read_at',
        'created_at',
    ];

    protected $casts = [
        'read_at' => 'datetime nullable',
        'created_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
