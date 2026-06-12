<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    use HasFactory;

    protected $table = 'tbl_widgets';

    protected $fillable = [
        'widget',
        'widget_type',
        'data',
        'status',
        'created_at',
    ];
}
