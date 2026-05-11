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

    public function getImageUrlAttribute()
    {
        if (!$this->snapshot_path) return null;
        
        $node = $this->cctv->server;
        // Mapping IP Node ke Prefix Nginx (node1 atau node2)
        $nodePrefix = ($node && $node->ip_address == '10.69.69.41') ? 'node1' : 'node2';
        
        return "/{$nodePrefix}/storage/recordings/snapshots/{$this->snapshot_path}";
    }
}
