<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odp extends Model
{
    use HasFactory;

    protected $table = 'tbl_odps';

    protected $fillable = [
        'name',
        'code',
        'location',
        'description',
        'status',
        'created_at',
    ];
}
