<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEvent extends Model
{
    use HasFactory;

    protected $table = 'cctv_ai_events';

    protected $fillable = [
        'cctv_id',
        'recording_id',
        'event_type',
        'object_type',
        'color',
        'confidence',
        'snapshot_url',
        'snapshot_before_url',
        'snapshot_after_url',
        'bounding_box',
        'event_time',
        'last_seen_time',
        'narrative',
        'metadata',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'last_seen_time' => 'datetime',
        'confidence' => 'float',
        'bounding_box' => 'array',
        'metadata' => 'array',
    ];

    public function cctv(): BelongsTo
    {
        return $this->belongsTo(Cctv::class);
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }

    /**
     * Scope query untuk pencarian & filtering
     */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['cctv_id'])) {
            $query->where('cctv_id', $filters['cctv_id']);
        }

        if (!empty($filters['event_type']) && $filters['event_type'] !== 'all') {
            $query->where('event_type', $filters['event_type']);
        }

        if (!empty($filters['object_type']) && $filters['object_type'] !== 'all') {
            if ($filters['object_type'] === 'laptop') {
                $query->whereIn('object_type', ['laptop', 'cell phone']);
            } elseif ($filters['object_type'] === 'backpack') {
                $query->whereIn('object_type', ['backpack', 'handbag', 'suitcase']);
            } elseif ($filters['object_type'] === 'car') {
                $query->whereIn('object_type', ['car', 'truck', 'bus']);
            } else {
                $query->where('object_type', $filters['object_type']);
            }
        }

        if (!empty($filters['date'])) {
            $query->whereDate('event_time', $filters['date']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . strtolower($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(narrative) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(object_type) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(color) LIKE ?', [$search]);
            });
        }

        return $query;
    }
}
