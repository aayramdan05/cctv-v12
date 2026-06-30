<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'cctv_id',
        'details',
        'ip_address',
    ];

    /**
     * Get the user who performed this activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the CCTV referenced in this activity (if any).
     */
    public function cctv()
    {
        return $this->belongsTo(Cctv::class);
    }
}
