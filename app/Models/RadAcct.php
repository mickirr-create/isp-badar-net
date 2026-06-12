<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadAcct extends Model
{
    use HasFactory;

    protected $table = 'rad_acct';

    protected $fillable = [
        'radacctid',
        'acctsessionid',
        'acctsessiontime',
        'acctinputoctets',
        'acctoutputoctets',
        'acctstopcause',
        'username',
        'framedipaddress',
        'nasipaddress',
        'nasportid',
        'acctstarttime',
        'acctstoptime',
        'created_at',
    ];

    protected $casts = [
        'acctstarttime' => 'datetime',
        'acctstoptime' => 'datetime',
        'created_at' => 'datetime',
    ];
}
