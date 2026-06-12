<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bandwidth extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tbl_bandwidth';

    protected $fillable = [
        'name_bw',
        'rate_down',
        'rate_up',
        'rate_down_unit',
        'rate_up_unit',
        'burst',
    ];

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class, 'id_bw');
    }
}
