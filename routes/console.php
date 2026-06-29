<?php

use Illuminate\Support\Facades\Schedule;

// --- SCHEDULER MONITORING CCTV ---

// Mengecek status koneksi (Online/Offline) seluruh kamera CCTV setiap 1 menit
Schedule::command('cctv:check-status')->everyMinute();
