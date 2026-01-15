<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import Wajib

class Cctv extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'server_id', // Pastikan kolom ini ada di $fillable
        'kode_cctv',
        'nama_cctv',
        'ip',
        'rtsp_url',
        'rtsp_url_sub',
        'rtsp_user',
        'rtsp_password',
        'status',
        'recorder_ip', // Kolom legacy (bisa dihapus nanti jika sudah full server_id)
    ];

    protected $casts = [
        'rtsp_password' => 'encrypted',
    ];

    // --- RELASI ---

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeAccessibleByAuth($query)
    {
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // Return kosong jika tidak login
        }

        // 1. Admin & Operator Pusat: LIHAT SEMUA
        if ($user->role === 'admin' || $user->role === 'operator') {
            return $query; 
        }

        // 2. Operator Fakultas: LIHAT SESUAI FAKULTAS
        if ($user->role === 'faculty_operator') {
            return $query->whereHas('building', function ($q) use ($user) {
                $q->where('fakultas', $user->faculty);
            });
        }

        // 3. User Biasa & API Viewer: HANYA YANG DI-ASSIGN (PIVOT)
        if ($user->role === 'user' || $user->role === 'api_viewer') {
            return $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query->whereRaw('1 = 0'); // Default kosong
    }

    // --- ACCESSORS ---

    public function getStreamUrlAttribute()
    {
        $url = $this->rtsp_url;
        $user = $this->rtsp_user;
        $pass = $this->rtsp_password; 

        if (empty($user) || empty($pass)) return $url;

        $userEncoded = rawurlencode($user);
        $passEncoded = rawurlencode($pass);

        if (str_starts_with($url, 'http://')) {
            $cleanPath = substr($url, 7); 
            return "http://{$userEncoded}:{$passEncoded}@{$cleanPath}";
        }

        if (str_starts_with($url, 'rtsp://')) {
            $cleanPath = substr($url, 7);
            return "rtsp://{$userEncoded}:{$passEncoded}@{$cleanPath}";
        }

        return "rtsp://{$userEncoded}:{$passEncoded}@{$url}";
    }

    public function getLiveStreamUrlAttribute()
    {
        $alias = "camera_" . $this->id;
        $params = "&mode=mse,webrtc,mjpeg"; // Prioritas mode

        // Cek apakah kamera ini terhubung ke Server Node tertentu?
        if ($this->server) {
            // Logika Mapping: Server ID 1 -> /node1/
            $nodePrefix = "/node" . $this->server->id; 
            return "{$nodePrefix}/stream.html?src={$alias}{$params}";
        }

        // Fallback: Jika tidak ada server_id, gunakan path standar (Master)
        return "/stream.html?src={$alias}{$params}";
    }
    /**
     * HELPER BARU: Generate URL Rekaman (Multi-Node Support)
     * Ini fungsi yang menyebabkan error 500 jika hilang
     */
    public function getRecordingUrl($date, $filename)
    {
        // Cek apakah kamera ini terhubung ke Server Node tertentu?
        if ($this->server) {
            // Mapping: Server ID 1 -> /node1/
            $nodePrefix = "/node" . $this->server->id; 
            
            // Hasil: https://cctv.unpad.net/node1/storage/recordings/...
            // (Nginx akan melempar ini ke server yang sesuai)
            return "{$nodePrefix}/storage/recordings/{$date}/{$filename}";
        }

        // Fallback: Jika tidak ada server, gunakan asset lokal (Server Master/Single)
        return "/storage/recordings/{$date}/{$filename}";
    }
}