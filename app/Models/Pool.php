<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pool extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tbl_pool';

    protected $fillable = [
        'pool_name',
        'routers',
        'ip_address',
        'description',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'routers', 'name');
    }
}
