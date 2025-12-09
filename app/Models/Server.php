<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = [
        'name', 'ip_address', 'location', 'is_active', 'description'
    ];

    // Relasi: Satu Server punya banyak CCTV
    public function cctvs()
    {
        return $this->hasMany(Cctv::class);
    }
}