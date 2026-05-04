<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    protected $fillable = [
        'cctv_id',
        'date',
        'filename',
        'start_time',
        'duration',
        'size_mb'
    ];

    public function cctv()
    {
        return $this->belongsTo(Cctv::class);
    }
}
