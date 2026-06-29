<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    /**
     * Show the main map view.
     */
    public function index()
    {
        return view('map.index');
    }

    /**
     * Get Outdoor CCTV data from local database.
     */
    public function getCctvData()
    {
        try {
            // Hanya ambil kamera Outdoor yang punya koordinat dan sesuai hak akses user
            $cctvs = Cctv::accessibleByAuth()
                        ->where('penempatan', 'Outdoor')
                        ->whereNotNull('lat')
                        ->whereNotNull('lng')
                        ->get()
                        ->map(function($cctv) {
                            return [
                                'id' => $cctv->id,
                                'name' => $cctv->nama_cctv,
                                'building' => $cctv->building ? $cctv->building->nama_gedung : 'Area Kampus',
                                'lat' => (float) $cctv->lat,
                                'lng' => (float) $cctv->lng,
                                'status' => $cctv->status,
                                'stream_url' => $cctv->live_stream_url
                            ];
                        });

            return response()->json($cctvs);
        } catch (\Exception $e) {
            Log::error('Map CCTV Data Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil data kamera.'], 500);
        }
    }

    /**
     * Proxy the stream.html to bypass Content-Security-Policy (CSP) frame-ancestors block.
     */
    public function streamProxy(Request $request)
    {
        $url = $request->query('url');
        
        if (!$url) {
            return response('Missing URL parameter', 400);
        }

        // Handle relative URLs (add current host)
        if (str_starts_with($url, '/')) {
            $url = $request->getSchemeAndHttpHost() . $url;
        }

        try {
            // Gunakan timeout agar tidak hang
            $response = Http::withoutVerifying()->timeout(10)->get($url);
            
            if (!$response->successful()) {
                return response('Failed to load stream from source', $response->status());
            }

            $html = $response->body();

            // Extract base URL (e.g. https://cctv.unpad.net/node1/)
            $parsed = parse_url($url);
            $base = $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
            $path = isset($parsed['path']) ? dirname($parsed['path']) . '/' : '/';
            $baseUrl = $base . $path;
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';

            // Inject base tag so relative scripts/css load from the source domain
            $html = str_replace('<head>', "<head>\n    <base href=\"{$baseUrl}\">", $html);
            
            // Fix the location.href and location.search in the go2rtc JS
            $html = str_replace('location.href', "'{$url}'", $html);
            $html = str_replace('location.search', "'{$query}'", $html);

            return response($html)->withHeaders([
                'Content-Type' => 'text/html',
                'Content-Security-Policy' => '' // Strip out CSP to allow iframe embedding locally
            ]);

        } catch (\Exception $e) {
            Log::error('CCTV Stream Proxy Error: ' . $e->getMessage());
            return response('Stream proxy error', 500);
        }
    }

    /**
     * Update coordinates only (Admin Quick Edit)
     */
    public function updateCoordinates(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'id' => 'required|exists:cctvs,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $cctv = Cctv::findOrFail($request->id);
        $cctv->update([
            'lat' => $request->lat,
            'lng' => $request->lng
        ]);

        return response()->json(['message' => 'Koordinat berhasil diperbarui']);
    }
}
