<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerField extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tbl_customers_fields';

    protected $fillable = [
        'customer_id',
        'field_name',
        'field_value',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
