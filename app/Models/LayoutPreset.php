<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayoutPreset extends Model
{
    protected $fillable = ['user_id', 'name', 'grid_size', 'slots'];

    protected $casts = [
        'slots' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
