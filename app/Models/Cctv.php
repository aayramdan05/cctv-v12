<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Cctv extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        // Trigger Real-time Sync ke Node saat data berubah
        static::saved(function ($cctv) {
            try {
                // Beritahu Node spesifik atau semua node untuk sync
                $payload = $cctv->server ? $cctv->server->ip_address : 'ALL';
                DB::statement("NOTIFY cctv_update, '{$payload}'");
            } catch (\Exception $e) {
                // Jangan sampai error notify menggagalkan save
            }
        });

        static::created(function ($cctv) {
            try {
                DB::table('activity_logs')->insert([
                    'user_id'       => auth()->id(),
                    'activity_type' => 'cctv_add',
                    'cctv_id'       => $cctv->id,
                    'details'       => json_encode([
                        'nama_cctv' => $cctv->nama_cctv,
                        'kode_cctv' => $cctv->kode_cctv,
                        'ip'        => $cctv->ip,
                    ]),
                    'ip_address'    => request()->ip(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } catch (\Exception $e) {}
        });

        static::updated(function ($cctv) {
            try {
                // 1. Log Camera status change (Up/Down)
                if ($cctv->isDirty('status')) {
                    $newStatus = $cctv->status;
                    $oldStatus = $cctv->getOriginal('status');
                    $activityType = ($newStatus === 'offline') ? 'camera_down' : 'camera_up';
                    
                    DB::table('activity_logs')->insert([
                        'user_id'       => auth()->id(), // Bisa null jika cron/cli
                        'activity_type' => $activityType,
                        'cctv_id'       => $cctv->id,
                        'details'       => json_encode([
                            'nama_cctv'  => $cctv->nama_cctv,
                            'ip'         => $cctv->ip,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                        ]),
                        'ip_address'    => request()->ip(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                // 2. Log general CCTV edit (jika field selain status berubah)
                $dirtyFields = $cctv->getDirty();
                // Hapus status & updated_at agar tidak mentrigger log edit saat status saja yang berubah
                unset($dirtyFields['status'], $dirtyFields['updated_at']);
                
                if (!empty($dirtyFields)) {
                    // Filter password
                    unset($dirtyFields['rtsp_password'], $dirtyFields['onvif_password']);
                    
                    DB::table('activity_logs')->insert([
                        'user_id'       => auth()->id(),
                        'activity_type' => 'cctv_edit',
                        'cctv_id'       => $cctv->id,
                        'details'       => json_encode([
                            'nama_cctv' => $cctv->nama_cctv,
                            'changes'   => $dirtyFields,
                        ]),
                        'ip_address'    => request()->ip(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            } catch (\Exception $e) {}
        });

        static::deleted(function ($cctv) {
            try {
                DB::table('activity_logs')->insert([
                    'user_id'       => auth()->id(),
                    'activity_type' => 'cctv_delete',
                    'cctv_id'       => null,
                    'details'       => json_encode([
                        'nama_cctv' => $cctv->nama_cctv,
                        'kode_cctv' => $cctv->kode_cctv,
                    ]),
                    'ip_address'    => request()->ip(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                
                DB::statement("NOTIFY cctv_update, 'ALL'");
            } catch (\Exception $e) {
            }
        });
    }

    protected $fillable = [
        'building_id',
        'server_id',
        'kode_cctv',
        'nama_cctv',
        'ip',
        'rtsp_url',
        'rtsp_url_sub',
        'rtsp_user',
        'rtsp_password',
        'onvif_port',
        'onvif_user',
        'onvif_password',
        'status',
        'recorder_ip',
        'penempatan',
        'lat',
        'lng',
    ];

    protected $casts = [
        'rtsp_password' => 'encrypted',
        'onvif_password' => 'encrypted',
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

    public function recordings()
    {
        return $this->hasMany(Recording::class);
    }

    public function scopeAccessibleByAuth($query)
    {
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // Return kosong jika tidak login
        }

        // 1. Superadmin, Admin & Operator Pusat: LIHAT SEMUA
        if (in_array($user->role, ['superadmin', 'admin', 'operator'])) {
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

    public function getMerkAttribute()
    {
        $url = strtolower($this->rtsp_url ?? '');
        if (str_contains($url, 'streaming/channels')) {
            return 'Hikvision';
        }
        if (str_contains($url, 'cam/realmonitor')) {
            return 'Dahua / IMOU';
        }
        if (str_contains($url, 'stream1') || str_contains($url, 'stream2')) {
            return 'TP-Link VIGI';
        }
        if (str_contains($url, 'unicast/c1/s0/live')) {
            return 'Uniview (UNV)';
        }
        if (str_contains($url, 'h264/ch1/main/av_stream')) {
            return 'Ezviz';
        }
        if (str_contains($url, 'live/ch00_0')) {
            return 'SPC';
        }
        if (str_contains($url, 'live/ch0')) {
            return 'Bardi / Tuya';
        }
        if (str_contains($url, 'snw/live/cam/realmonitor')) {
            return 'Hanwha / Samsung';
        }
        return 'Generic / ONVIF';
    }

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
     * URL HLS (m3u8) untuk Mobile (Android/iOS)
     */
    public function getHlsStreamUrlAttribute()
    {
        $alias = "camera_" . $this->id;
        
        if ($this->server) {
            $nodePrefix = "/node" . $this->server->id; 
            return "{$nodePrefix}/api/stream.m3u8?src={$alias}";
        }

        return "/api/stream.m3u8?src={$alias}";
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