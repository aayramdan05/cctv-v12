<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Models\Building;
use Illuminate\View\View;
// use Illuminate\Support\Facades\Http; // Tidak lagi diperlukan
// use Illuminate\Support\Facades\Log;  // Tidak lagi diperlukan untuk Go2RTC

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // 1. STATISTIK UTAMA
        $totalCctv = Cctv::accessibleByAuth()->count();
        $activeCctv = Cctv::accessibleByAuth()->where('status', 'online')->count();
        $offlineCctv = Cctv::accessibleByAuth()->where('status', 'offline')->count();

        // 2. DATA GEDUNG
        if ($user->role === 'faculty_operator') {
            $buildings = Building::where('fakultas', $user->faculty)
                        ->withCount('cctvs')->get();
            $totalGedung = Building::where('fakultas', $user->faculty)->count();
        } else {
            $buildings = Building::withCount('cctvs')
                        ->orderBy('cctvs_count', 'desc')->take(6)->get();
            $totalGedung = Building::count();
        }

        // 3. LIVE PREVIEW (3 Kamera Terakhir)
        // Kita tetap memuat relasi 'server' agar Accessor 'live_stream_url' di Model
        // bisa bekerja efisien menentukan path /node1/, /node2/, dll.
        $latestCctvs = Cctv::accessibleByAuth()
                           ->with(['building', 'server']) 
                           ->latest()
                           ->take(3)
                           ->get();

        // REVISI: BAGIAN REGISTER STREAM DIHAPUS
        // Karena registrasi stream sudah ditangani oleh SyncGo2RTCConfig.php (Background Service)
        // Dashboard hanya bertugas menampilkan URL saja via Accessor Model.

        // 4. ALERTS
        $alerts = $this->getAlerts();

        return view('dashboard', compact(
            'totalCctv', 'totalGedung', 'activeCctv', 'offlineCctv',
            'buildings', 'latestCctvs', 'alerts'
        ));
    }

    /**
     * Logic Alert
     */
    private function getAlerts()
    {
        $alerts = collect();

        // A. Alert Ungu: Kamera Baru (2 jam terakhir)
        $newCameras = Cctv::accessibleByAuth()
            ->with('building')
            ->where('created_at', '>=', now()->subHours(2))
            ->get();

        foreach($newCameras as $cam) {
            $alerts->push([
                'type' => 'new',
                'icon' => 'fa-video',
                'title' => 'New Camera Added',
                'message' => "{$cam->building->nama_gedung} - {$cam->nama_cctv}",
                'time' => $cam->created_at->diffForHumans(),
                'sort_time' => $cam->created_at
            ]);
        }

        // B. Alert Merah: Kamera Offline
        $offlineCameras = Cctv::accessibleByAuth()
            ->with('building')
            ->where('status', 'offline')
            ->get();

        foreach($offlineCameras as $cam) {
            $alerts->push([
                'type' => 'offline',
                'icon' => 'fa-exclamation-circle',
                'title' => 'Camera Offline',
                'message' => "{$cam->building->nama_gedung} - {$cam->nama_cctv}",
                'time' => $cam->updated_at->diffForHumans(),
                'sort_time' => $cam->updated_at
            ]);
        }

        // C. Alert Hijau: Kamera Restored
        $onlineCameras = Cctv::accessibleByAuth()
            ->with('building')
            ->where('status', 'online')
            ->where('updated_at', '>=', now()->subHour())
            ->get();

        foreach($onlineCameras as $cam) {
            if($cam->created_at < now()->subHours(2)) { 
                $alerts->push([
                    'type' => 'online',
                    'icon' => 'fa-check-circle',
                    'title' => 'Camera Restored',
                    'message' => "{$cam->building->nama_gedung} - {$cam->nama_cctv}",
                    'time' => $cam->updated_at->diffForHumans(),
                    'sort_time' => $cam->updated_at
                ]);
            }
        }

        return $alerts->sortByDesc('sort_time')->take(5);
    }
}