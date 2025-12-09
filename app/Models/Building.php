<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'kode_gedung',
        'nama_gedung',
        'fakultas',
    ];

    /**
     * Relasi ke CCTV
     */
    public function cctvs()
    {
        return $this->hasMany(Cctv::class);
    }
}