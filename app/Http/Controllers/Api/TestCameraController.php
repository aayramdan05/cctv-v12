<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class TestCameraController extends Controller
{
    public function test(Request $request)
    {
        $request->validate([
            'rtsp_url' => 'required|string',
        ]);

        // 1. Ambil Data
        $url = $request->rtsp_url;
        $user = $request->rtsp_user;
        $pass = $request->rtsp_password;

        // 2. RAKIT URL (Merge Credentials)
        // Jika user & pass diisi, kita gabungkan ke URL
        if (!empty($user) && !empty($pass)) {
            
            // Encode agar karakter spesial (seperti @ di password) aman
            $userEncoded = rawurlencode($user);
            $passEncoded = rawurlencode($pass);

            // Cek Protokol (RTSP atau HTTP)
            if (str_starts_with($url, 'rtsp://')) {
                $cleanPath = substr($url, 7); // Buang rtsp://
                $url = "rtsp://{$userEncoded}:{$passEncoded}@{$cleanPath}";
            } 
            elseif (str_starts_with($url, 'http://')) {
                $cleanPath = substr($url, 7); // Buang http://
                $url = "http://{$userEncoded}:{$passEncoded}@{$cleanPath}";
            }
            // Jika user lupa nulis protokol, anggap RTSP
            else {
                $url = "rtsp://{$userEncoded}:{$passEncoded}@{$url}";
            }
        }

        // 3. Siapkan Path Temp
        $tempImage = 'test_' . time() . '.jpg';
        $storagePath = storage_path('app/public/temp/' . $tempImage);
        
        if (!file_exists(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0775, true);
        }

        // 4. Perintah FFmpeg
        $command = [
            'ffmpeg',
            '-y',
            '-rtsp_transport', 'tcp', // Wajib TCP
            '-timeout', '8000000',    // Timeout 8 detik (biar sempat handshake)
            '-i', $url,               // URL yang sudah digabung password
            '-ss', '00:00:01',
            '-frames:v', '1',
            '-q:v', '2',
            $storagePath
        ];

        // Jalankan
        $process = Process::timeout(15)->run($command);

        if ($process->successful()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Koneksi Berhasil! Kamera Online.',
                'snapshot_url' => asset('storage/temp/' . $tempImage)
            ]);
        } else {
            // Kirim error log untuk debug (opsional)
            // \Log::error("Test Cam Fail: " . $process->errorOutput());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Terhubung. Pastikan User/Pass benar.',
            ], 422);
        }
    }
}