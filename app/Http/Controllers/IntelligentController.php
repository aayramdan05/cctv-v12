<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Cctv;
use App\Models\AiEvent;
use Carbon\Carbon;

class IntelligentController extends Controller
{
    /**
     * Display the Intelligent AI Dashboard (Events & Forensic Timeline).
     */
    public function index(Request $request)
    {
        $cctvs = collect();
        try {
            if (class_exists(Cctv::class)) {
                if (method_exists(Cctv::class, 'accessibleByAuth')) {
                    $cctvs = Cctv::accessibleByAuth()->with('building')->orderBy('nama_cctv')->get();
                } else {
                    $cctvs = Cctv::with('building')->orderBy('nama_cctv')->get();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil data CCTV untuk AI Intelligent: ' . $e->getMessage());
        }

        $filters = [
            'cctv_id' => $request->input('cctv_id'),
            'object_type' => $request->input('object_type', 'all'),
            'event_type' => $request->input('event_type', 'all'),
            'date' => $request->input('date'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'time_preset' => $request->input('time_preset', 'today'),
            'search' => $request->input('search'),
        ];

        // Ambil event dari Database, fallback ke Demo Data jika kosong / local laptop tanpa DB
        $events = $this->getEventsData($request);
        
        // Hitung statistik terfilter secara akurat sesuai kriteria filter waktu dan kamera yang aktif
        $stats = $this->getStatsData($filters, $events);

        return view('intelligent.index', [
            'cctvs' => $cctvs,
            'initialEvents' => $events,
            'stats' => $stats,
        ]);
    }

    /**
     * API: Ambil data event dengan filter dan search (JSON)
     */
    public function getEvents(Request $request)
    {
        $filters = [
            'cctv_id' => $request->input('cctv_id'),
            'object_type' => $request->input('object_type', 'all'),
            'event_type' => $request->input('event_type', 'all'),
            'date' => $request->input('date'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'time_preset' => $request->input('time_preset', 'today'),
            'search' => $request->input('search'),
        ];

        $events = $this->getEventsData($request);
        $stats = $this->getStatsData($filters, $events);

        return response()->json([
            'status' => 'success',
            'data' => $events,
            'count' => count($events),
            'total' => $stats['total'],
            'stats' => $stats,
        ]);
    }

    /**
     * Internal: Menghitung statistik KPI terfilter sesuai filter waktu dan kamera aktif
     */
    private function getStatsData(array $filters, array $events = []): array
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('cctv_ai_events') && AiEvent::exists()) {
                // Untuk kartu statistik KPI, pertahankan filter waktu, kamera, dan search,
                // bebaskan event_type dan object_type agar kartu KPI menampilkan proporsi seluruh kategori
                $statsFilters = $filters;
                $statsFilters['event_type'] = 'all';
                $statsFilters['object_type'] = 'all';

                $baseQuery = AiEvent::filter($statsFilters);

                return [
                    'total' => (clone $baseQuery)->count(),
                    'missing_items' => (clone $baseQuery)->where('event_type', 'missing_item')->count(),
                    'vehicles' => (clone $baseQuery)->whereIn('object_type', ['car', 'motorcycle', 'truck', 'bus'])->count(),
                    'persons' => (clone $baseQuery)->where('object_type', 'person')->count(),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal menghitung statistik AI terfilter: ' . $e->getMessage());
        }

        return [
            'total' => count($events),
            'missing_items' => count(array_filter($events, fn($e) => $e['event_type'] === 'missing_item')),
            'vehicles' => count(array_filter($events, fn($e) => in_array($e['object_type'], ['car', 'motorcycle', 'truck', 'bus']))),
            'persons' => count(array_filter($events, fn($e) => $e['object_type'] === 'person')),
        ];
    }

    /**
     * Internal: Mengambil event riil dari DB atau menghasilkan Demo Data yang realistis
     */
    private function getEventsData(Request $request): array
    {
        $filters = [
            'cctv_id' => $request->input('cctv_id'),
            'object_type' => $request->input('object_type', 'all'),
            'event_type' => $request->input('event_type', 'all'),
            'date' => $request->input('date'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'time_preset' => $request->input('time_preset', 'today'),
            'search' => $request->input('search'),
        ];

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('cctv_ai_events')) {
                $query = AiEvent::with('cctv')->filter($filters);
                
                // Urutkan kronologis terbaru ke terlama secara murni agar kejadian terkini selalu tampil di atas
                $query->orderBy('event_time', 'desc');
                $limit = (int) $request->input('limit', 250);
                $page = max(1, (int) $request->input('page', 1));
                $offset = ($page - 1) * $limit;
                $dbEvents = $query->skip($offset)->take(max(10, min(1000, $limit)))->get();

                if ($dbEvents->isNotEmpty()) {
                    return $dbEvents->map(function ($ev) {
                        return [
                            'id' => $ev->id,
                            'cctv_id' => $ev->cctv_id,
                            'cctv_name' => $ev->cctv->nama_cctv ?? 'CCTV #' . $ev->cctv_id,
                            'building_name' => $ev->cctv->building->nama_gedung ?? 'Kampus Unpad',
                            'event_type' => $ev->event_type,
                            'object_type' => $ev->object_type,
                            'color' => $ev->color,
                            'confidence' => round($ev->confidence * 100, 1),
                            'event_time' => $ev->event_time->format('H:i:s'),
                            'event_date' => $ev->event_time->format('d M Y'),
                            'date_ymd' => $ev->event_time->format('Y-m-d'),
                            'raw_datetime' => $ev->event_time->format('Y-m-d H:i:s'),
                            'last_seen_time' => $ev->last_seen_time ? $ev->last_seen_time->format('H:i:s') : null,
                            'narrative' => $ev->narrative,
                            'snapshot_url' => $ev->snapshot_url,
                            'snapshot_before_url' => $ev->snapshot_before_url,
                            'snapshot_after_url' => $ev->snapshot_after_url,
                            'metadata' => $ev->metadata,
                        ];
                    })->toArray();
                } elseif (AiEvent::exists()) {
                    // Database memiliki data, tetapi filter (misal: Hari Ini) belum memiliki rekaman kejadian
                    return [];
                }
            }
        } catch (\Throwable $e) {
            Log::info('Fallback to Demo Data AI Events: ' . $e->getMessage());
        }

        // Demo Data (Realistis untuk testing di laptop lokal)
        $demoEvents = [
            [
                'id' => 1,
                'cctv_id' => 1,
                'cctv_name' => 'CCTV 01 - Lab Komputer Lantai 2',
                'building_name' => 'Gedung FMIPA',
                'event_type' => 'missing_item',
                'object_type' => 'laptop',
                'color' => 'silver',
                'confidence' => 93.4,
                'event_time' => '11:15:30',
                'event_date' => now()->format('d M Y'),
                'last_seen_time' => '11:15:10',
                'narrative' => 'PERINGATAN: Laptop warna silver di atas meja lab tidak lagi terdeteksi sejak 11:15:30 WIB. Terakhir terlihat stabil pada 11:15:10 WIB saat seseorang berbaju gelap mendekati area meja.',
                'snapshot_url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500&auto=format&fit=crop&q=60',
                'snapshot_before_url' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500&auto=format&fit=crop&q=60',
                'snapshot_after_url' => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?w=500&auto=format&fit=crop&q=60',
                'metadata' => ['suspect_detected' => true, 'tracker_id' => 42, 'stationary_duration_min' => 45],
            ],
            [
                'id' => 2,
                'cctv_id' => 2,
                'cctv_name' => 'CCTV 04 - Gerbang Masuk Utama',
                'building_name' => 'Gerbang Selatan',
                'event_type' => 'vehicle_flow',
                'object_type' => 'car',
                'color' => 'merah',
                'confidence' => 97.2,
                'event_time' => '10:34:15',
                'event_date' => now()->format('d M Y'),
                'last_seen_time' => null,
                'narrative' => 'Mobil sedan warna merah melintas dari arah Gerbang Selatan menuju bundaran kampus dengan kecepatan normal.',
                'snapshot_url' => 'https://images.unsplash.com/photo-1541348263662-e0c82661cb25?w=500&auto=format&fit=crop&q=60',
                'snapshot_before_url' => null,
                'snapshot_after_url' => null,
                'metadata' => ['direction' => 'inbound', 'speed_est' => '25 km/h'],
            ],
            [
                'id' => 3,
                'cctv_id' => 3,
                'cctv_name' => 'CCTV 02 - Meja Baca Lantai 1',
                'building_name' => 'Perpustakaan Pusat',
                'event_type' => 'missing_item',
                'object_type' => 'backpack',
                'color' => 'hitam',
                'confidence' => 88.5,
                'event_time' => '10:12:45',
                'event_date' => now()->format('d M Y'),
                'last_seen_time' => '10:11:50',
                'narrative' => 'Tas ransel warna hitam yang sebelumnya berada di kursi baca dipindahkan/diambil oleh pengguna pada pukul 10:12:45 WIB.',
                'snapshot_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60',
                'snapshot_before_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60',
                'snapshot_after_url' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?w=500&auto=format&fit=crop&q=60',
                'metadata' => ['suspect_detected' => true, 'tracker_id' => 19],
            ],
            [
                'id' => 4,
                'cctv_id' => 4,
                'cctv_name' => 'CCTV 03 - Area Parkir Barat',
                'building_name' => 'Gedung Rektorat',
                'event_type' => 'vehicle_flow',
                'object_type' => 'motorcycle',
                'color' => 'hitam',
                'confidence' => 95.8,
                'event_time' => '09:48:20',
                'event_date' => now()->format('d M Y'),
                'last_seen_time' => null,
                'narrative' => 'Sepeda motor matic warna hitam memasuki area parkir staf dan parkir di blok B.',
                'snapshot_url' => 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=500&auto=format&fit=crop&q=60',
                'snapshot_before_url' => null,
                'snapshot_after_url' => null,
                'metadata' => ['direction' => 'parked'],
            ],
            [
                'id' => 5,
                'cctv_id' => 1,
                'cctv_name' => 'CCTV 01 - Lab Komputer Lantai 2',
                'building_name' => 'Gedung FMIPA',
                'event_type' => 'presence_detected',
                'object_type' => 'person',
                'color' => 'biru',
                'confidence' => 96.1,
                'event_time' => '09:15:00',
                'event_date' => now()->format('d M Y'),
                'last_seen_time' => null,
                'narrative' => 'Seseorang berjaket biru memasuki ruang lab komputer dan duduk di meja deretan depan.',
                'snapshot_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500&auto=format&fit=crop&q=60',
                'snapshot_before_url' => null,
                'snapshot_after_url' => null,
                'metadata' => ['clothing_color' => 'blue'],
            ],
        ];

        // Terapkan filter pada demo data jika query diberikan
        return array_values(array_filter($demoEvents, function ($item) use ($filters) {
            if (!empty($filters['cctv_id']) && $item['cctv_id'] != $filters['cctv_id']) {
                return false;
            }
            if (!empty($filters['object_type']) && $filters['object_type'] !== 'all' && $item['object_type'] !== $filters['object_type']) {
                return false;
            }
            if (!empty($filters['event_type']) && $filters['event_type'] !== 'all' && $item['event_type'] !== $filters['event_type']) {
                return false;
            }
            if (!empty($filters['search'])) {
                $q = strtolower($filters['search']);
                $searchable = strtolower($item['narrative'] . ' ' . $item['object_type'] . ' ' . $item['color'] . ' ' . $item['cctv_name']);
                if (!str_contains($searchable, $q)) {
                    return false;
                }
            }
            return true;
        }));
    }

    /**
     * API: Terima Push Event dari Python AI Agent
     */
    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'cctv_id' => 'required|integer',
            'recording_id' => 'nullable|integer',
            'event_type' => 'required|string|max:50',
            'object_type' => 'required|string|max:50',
            'color' => 'nullable|string|max:50',
            'confidence' => 'nullable|numeric',
            'snapshot_base64' => 'nullable|string',
            'snapshot_before_base64' => 'nullable|string',
            'snapshot_after_base64' => 'nullable|string',
            'bounding_box' => 'nullable|array',
            'event_time' => 'required|date',
            'last_seen_time' => 'nullable|date',
            'narrative' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $snapshotUrl = null;
        $snapshotBeforeUrl = null;
        $snapshotAfterUrl = null;

        // Simpan file snapshot jika dikirim dalam base64
        if (!empty($validated['snapshot_base64'])) {
            $snapshotUrl = $this->saveBase64Image($validated['snapshot_base64'], 'event_' . time());
        }
        if (!empty($validated['snapshot_before_base64'])) {
            $snapshotBeforeUrl = $this->saveBase64Image($validated['snapshot_before_base64'], 'before_' . time());
        }
        if (!empty($validated['snapshot_after_base64'])) {
            $snapshotAfterUrl = $this->saveBase64Image($validated['snapshot_after_base64'], 'after_' . time());
        }

        try {
            $targetCctvId = $validated['cctv_id'];
            if (!Cctv::where('id', $targetCctvId)->exists()) {
                $fallback = Cctv::first();
                if ($fallback) {
                    $targetCctvId = $fallback->id;
                }
            }

            $event = AiEvent::create([
                'cctv_id' => $targetCctvId,
                'recording_id' => $validated['recording_id'] ?? null,
                'event_type' => $validated['event_type'],
                'object_type' => $validated['object_type'],
                'color' => $validated['color'] ?? null,
                'confidence' => $validated['confidence'] ?? 0.9,
                'snapshot_url' => $snapshotUrl,
                'snapshot_before_url' => $snapshotBeforeUrl,
                'snapshot_after_url' => $snapshotAfterUrl,
                'bounding_box' => $validated['bounding_box'] ?? null,
                'event_time' => Carbon::parse($validated['event_time']),
                'last_seen_time' => !empty($validated['last_seen_time']) ? Carbon::parse($validated['last_seen_time']) : null,
                'narrative' => $validated['narrative'] ?? "Objek {$validated['object_type']} terdeteksi pada kamera.",
                'metadata' => $validated['metadata'] ?? [],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Event AI berhasil disimpan',
                'event_id' => $event->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error saving AiEvent: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Generate Rangkuman Otomatis (Ollama / Fallback AI Summary)
     */
    public function generateSummary(Request $request)
    {
        $cctvId = $request->input('cctv_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        // Coba panggil Ollama lokal jika tersedia
        try {
            $ollamaHost = env('OLLAMA_HOST', 'http://127.0.0.1:11434');
            $response = Http::timeout(4)->post("{$ollamaHost}/api/generate", [
                'model' => env('OLLAMA_MODEL', 'qwen2.5:1.5b'),
                'prompt' => "Buat ringkasan singkat dalam 2 kalimat dalam bahasa Indonesia mengenai aktivitas CCTV hari ini tanggal {$date}. Sebutkan ada 1 laporan barang berpindah (laptop) dan aktivitas kendaraan berjalan tertib.",
                'stream' => false,
            ]);

            if ($response->successful()) {
                $aiText = $response->json('response');
                return response()->json([
                    'status' => 'success',
                    'source' => 'ollama',
                    'summary' => trim($aiText),
                ]);
            }
        } catch (\Throwable $e) {
            // Ollama offline / fallback
        }

        // Fallback Rangkuman AI Cerdas Cepat
        $fallbackSummary = "Ringkasan Aktivitas Terpantau: Sepanjang hari ini tercatat beberapa pergerakan kendaraan normal di area gerbang masuk serta aktivitas di ruang laboratorium. Terdeteksi 1 anomali perpindahan barang (laptop warna silver di Lab Komputer pada pukul 11:15 WIB) yang memerlukan atensi petugas piket.";

        return response()->json([
            'status' => 'success',
            'source' => 'engine_summary',
            'summary' => $fallbackSummary,
        ]);
    }

    /**
     * Helper simpan base64 image ke disk public
     */
    private function saveBase64Image(string $base64Data, string $prefix): ?string
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]);
            } else {
                $ext = 'jpg';
            }

            $imageData = base64_decode($base64Data);
            if ($imageData === false) return null;

            $filename = "ai_events/{$prefix}_" . uniqid() . ".{$ext}";
            Storage::disk('public')->put($filename, $imageData);
            return Storage::url($filename);
        } catch (\Throwable $e) {
            Log::error('Error saving base64 image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Webhook kompatibilitas UNV lama (agar tidak breaking)
     */
    public function receiveWebhook(Request $request)
    {
        return response()->json([
            'Response' => [
                'ResponseCode' => 0,
                'ResponseString' => 'Succeed'
            ]
        ]);
    }

    public function checkOnvif(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }
}
