<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    use HasFactory;

    protected $table = 'tbl_meta';

    protected $fillable = [
        'meta_key',
        'meta_value',
        'created_at',
    ];
}
