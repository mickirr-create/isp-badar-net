<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortPool extends Model
{
    use HasFactory;

    protected $table = 'tbl_port_pool';

    protected $fillable = [
        'port',
        'server',
        'status',
        'description',
    ];
}
