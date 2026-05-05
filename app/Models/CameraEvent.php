<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CameraEvent extends Model
{
    protected $fillable = [
        'cctv_id',
        'event_type',
        'event_time',
        'snapshot_path',
        'metadata',
        'is_read'
    ];

    protected $casts = [
        'metadata' => 'array',
        'event_time' => 'datetime',
        'is_read' => 'boolean'
    ];

    public function cctv()
    {
        return $this->belongsTo(Cctv::class);
    }
}
