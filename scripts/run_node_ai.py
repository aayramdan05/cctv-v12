#!/usr/bin/env python3
"""
Run Node AI - Standalone Batch & Live Watcher for CCTV AI Analytics
Unpad CCTV Monitoring System
Teknologi: Ultralytics YOLOv8 + Motion Pre-Filter + ByteTrack + Ollama LLM + OpenCV

Kegunaan:
1. Menjalankan pengujian AI Analytics pada 42 kamera CCTV di satu Node tanpa mengganggu proses recording.
2. Memindai folder rekaman CCTV (/var/www/html/storage/recordings/...) secara sekuensial (FIFO).
3. Menggunakan Motion Pre-Filter untuk menghemat 70-85% CPU.
4. Mengirimkan event deteksi ke Master Dashboard secara real-time.
"""

import os
import sys
import time
import glob
import argparse
from datetime import datetime
from dotenv import load_dotenv

# Prioritaskan CPU rendah (nice 15) di lingkungan Linux agar perekaman FFmpeg & streaming Go2RTC tidak terganggu
try:
    os.nice(15)
except Exception:
    pass

# Load environment variable
load_dotenv('/home/aay/cctv-scripts/.env')
load_dotenv('.env')

# Pastikan import script lokal
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
if SCRIPT_DIR not in sys.path:
    sys.path.insert(0, SCRIPT_DIR)

from ai_analyzer import CctvAiAnalyzer


def find_recording_files(base_dir, date_str=None, cctv_id=None, latest_only=True):
    """
    Mencari file rekaman CCTV (.mp4) di direktori penyimpanan lokal.
    Format path: {base_dir}/{YYYY-MM-DD}/cam_{id}_{timestamp}.mp4
    """
    if not os.path.exists(base_dir):
        print(f"❌ Direktori rekaman tidak ditemukan: {base_dir}")
        return []

    if date_str == 'today' or not date_str:
        date_str = datetime.now().strftime('%Y-%m-%d')

    target_folder = os.path.join(base_dir, date_str)
    if not os.path.exists(target_folder):
        # Jika folder hari ini belum ada, cari folder tanggal terbaru yang ada
        all_folders = sorted([f for f in glob.glob(os.path.join(base_dir, "*")) if os.path.isdir(f)], reverse=True)
        if all_folders:
            target_folder = all_folders[0]
            date_str = os.path.basename(target_folder)
            print(f"ℹ️ Folder hari ini belum ada. Menggunakan folder tanggal terbaru: {date_str}")
        else:
            print(f"❌ Tidak ada folder tanggal di: {base_dir}")
            return []

    pattern = os.path.join(target_folder, "*.mp4")
    all_files = glob.glob(pattern)

    if not all_files:
        print(f"⚠️ Tidak ditemukan file rekaman (.mp4) di: {target_folder}")
        return []

    # Filter berdasarkan CCTV ID jika dispesifikasikan
    if cctv_id:
        cam_prefix = f"cam_{cctv_id}_"
        all_files = [f for f in all_files if os.path.basename(f).startswith(cam_prefix)]

    # Kelompokkan per ID kamera
    cams_files = {}
    for f in all_files:
        base = os.path.basename(f)
        parts = base.split('_')
        if len(parts) >= 2 and parts[1].isdigit():
            cid = int(parts[1])
            cams_files.setdefault(cid, []).append(f)

    # Urutkan file berdasarkan waktu modifikasi terbaru
    for cid in cams_files:
        cams_files[cid].sort(key=lambda x: os.path.getmtime(x), reverse=True)

    selected_files = []
    if latest_only:
        # Ambil hanya 1 rekaman paling baru per kamera
        for cid in sorted(cams_files.keys()):
            selected_files.append((cid, cams_files[cid][0]))
    else:
        # Ambil semua rekaman per kamera
        for cid in sorted(cams_files.keys()):
            for f in cams_files[cid]:
                selected_files.append((cid, f))

    return selected_files


def watch_and_process(analyzer, base_dir, sample_fps, conf, use_motion_filter, motion_thresh, limit_sec):
    """
    Mode Watcher: Memantau folder rekaman secara real-time dan menganalisis rekaman baru
    begitu FFmpeg selesai menulis chunk 15 menit.
    """
    print(f"\n👀 [WATCHER MODE AKTIF] Memantau rekaman baru di: {base_dir}")
    print("Tekan Ctrl+C untuk berhenti.\n")

    processed_files = set()
    today_str = datetime.now().strftime('%Y-%m-%d')
    today_folder = os.path.join(base_dir, today_str)

    # Catat file yang sudah ada agar tidak dianalisa ulang
    if os.path.exists(today_folder):
        existing = glob.glob(os.path.join(today_folder, "*.mp4"))
        processed_files.update(existing)
        print(f"ℹ️ Terdeteksi {len(existing)} file rekaman lama (dilewati, menunggu rekaman baru)...")

    while True:
        try:
            today_str = datetime.now().strftime('%Y-%m-%d')
            today_folder = os.path.join(base_dir, today_str)

            if os.path.exists(today_folder):
                current_files = glob.glob(os.path.join(today_folder, "*.mp4"))
                for filepath in sorted(current_files, key=os.path.getmtime):
                    if filepath not in processed_files:
                        # Cek apakah file sudah selesai ditulis (ukuran stabil dalam 3 detik)
                        size1 = os.path.getsize(filepath)
                        time.sleep(3)
                        size2 = os.path.getsize(filepath)

                        if size1 == size2 and size2 > 1024 * 1024:  # Ukuran > 1MB dan stabil
                            print(f"\n🎬 [REKAMAN BARU SELESAI] {os.path.basename(filepath)} ({size2 / (1024*1024):.2f} MB)")
                            processed_files.add(filepath)

                            # Ekstrak CCTV ID
                            base = os.path.basename(filepath)
                            parts = base.split('_')
                            cam_id = int(parts[1]) if len(parts) >= 2 and parts[1].isdigit() else 1

                            t0 = time.time()
                            events = analyzer.process_video(
                                video_source=filepath,
                                sample_fps=sample_fps,
                                max_duration_sec=limit_sec,
                                conf_threshold=conf,
                                use_motion_filter=use_motion_filter,
                                min_motion_ratio=motion_thresh,
                                cctv_id=cam_id
                            )
                            dur = time.time() - t0
                            print(f"✅ Selesai analisa dalam {dur:.1f} detik. {len(events)} event dicatat ke Master.\n")
                        else:
                            # File masih dalam proses perekaman oleh FFmpeg, tunggu loop berikutnya
                            pass

            time.sleep(5)
        except KeyboardInterrupt:
            print("\n🛑 Watcher dihentikan oleh pengguna.")
            break
        except Exception as e:
            print(f"⚠️ Error watcher loop: {e}")
            time.sleep(5)


def main():
    parser = argparse.ArgumentParser(description="Unpad CCTV AI - Node Runner (YOLOv8 + Motion Pre-Filter)")
    parser.add_argument("--recordings-dir", type=str, default=os.getenv('RECORDINGS_PATH', '/var/www/html/storage/recordings'),
                        help="Path direktori rekaman video")
    parser.add_argument("--master-url", type=str, default=os.getenv('MASTER_URL', f"http://{os.getenv('DB_HOST', '10.69.69.21')}"),
                        help="URL Master Laravel API (contoh: http://10.69.69.21)")
    parser.add_argument("--ollama-url", type=str, default=os.getenv('OLLAMA_URL', 'http://127.0.0.1:11434'),
                        help="URL server Ollama lokal")
    parser.add_argument("--cctv-id", type=int, default=None,
                        help="Uji coba hanya pada 1 CCTV ID tertentu (contoh: --cctv-id 88)")
    parser.add_argument("--date", type=str, default='today',
                        help="Tanggal rekaman yang diuji (YYYY-MM-DD atau 'today')")
    parser.add_argument("--all-files", action="store_true",
                        help="Analisa semua file rekaman hari itu (bukan hanya 1 file terbaru per kamera)")
    parser.add_argument("--max-videos", type=int, default=None,
                        help="Batas maksimal video yang diproses dalam uji coba ini (contoh: --max-videos 5)")
    parser.add_argument("--limit-sec", type=int, default=None,
                        help="Batas durasi video (detik) per rekaman untuk pengujian cepat (contoh: --limit-sec 60)")
    parser.add_argument("--fps", type=int, default=1,
                        help="Sampling FPS (default: 1 FPS, optimal di CPU)")
    parser.add_argument("--conf", type=float, default=0.25,
                        help="Ambang batas deteksi confidence YOLO (default: 0.25)")
    parser.add_argument("--no-motion-filter", action="store_true",
                        help="Matikan fitur Motion Pre-filter")
    parser.add_argument("--motion-thresh", type=float, default=0.003,
                        help="Threshold gerakan pixel ratio (default: 0.003 / 0.3%%)")
    parser.add_argument("--watch", action="store_true",
                        help="Jalankan dalam mode Watcher (otomatis analisa file baru saat selesai direkam)")

    args = parser.parse_args()

    use_mf = not args.no_motion_filter

    print("=================================================================")
    print("   UNPAD CCTV AI ANALYZER - NODE ENGINE (42 KAMERA RUNNER)")
    print("=================================================================")
    print(f"Direktori Rekaman : {args.recordings_dir}")
    print(f"Master Laravel    : {args.master_url}")
    print(f"Sampling Rate     : {args.fps} FPS")
    print(f"Motion Pre-Filter : {'AKTIF (Hemat CPU 70-85%)' if use_mf else 'NONAKTIF'}")
    print(f"Confidence        : {args.conf}")
    print("=================================================================\n")

    # Inisialisasi CctvAiAnalyzer
    analyzer = CctvAiAnalyzer(
        model_weight='yolov8n.pt',
        cctv_id=args.cctv_id or 1,
        master_url=args.master_url,
        ollama_url=args.ollama_url
    )

    if args.watch:
        watch_and_process(
            analyzer=analyzer,
            base_dir=args.recordings_dir,
            sample_fps=args.fps,
            conf=args.conf,
            use_motion_filter=use_mf,
            motion_thresh=args.motion_thresh,
            limit_sec=args.limit_sec
        )
        return

    # Mode Batch Scan: Cari file rekaman yang ada
    print(f"🔍 Mencari file rekaman di: {args.recordings_dir} (Tanggal: {args.date})...")
    files_to_process = find_recording_files(
        base_dir=args.recordings_dir,
        date_str=args.date,
        cctv_id=args.cctv_id,
        latest_only=not args.all_files
    )

    if not files_to_process:
        print("\n❌ Tidak ada file rekaman yang dapat diproses.")
        print("Saran perbaikan:")
        print(f"1. Pastikan folder {args.recordings_dir} berisi subfolder tanggal (YYYY-MM-DD).")
        print("2. Gunakan flag --date YYYY-MM-DD jika ingin memeriksa rekaman kemarin/tanggal tertentu.")
        return

    if args.max_videos:
        files_to_process = files_to_process[:args.max_videos]

    total_cams = len(set(cid for cid, _ in files_to_process))
    print(f"✅ Ditemukan {len(files_to_process)} file rekaman dari total {total_cams} kamera CCTV.\n")

    total_events_found = 0
    start_all = time.time()

    for idx, (cid, video_file) in enumerate(files_to_process, 1):
        filename = os.path.basename(video_file)
        file_mb = os.path.getsize(video_file) / (1024 * 1024)
        print(f"\n=======================================================")
        print(f"[{idx}/{len(files_to_process)}] Kamera ID {cid} | File: {filename} ({file_mb:.1f} MB)")
        print(f"=======================================================")

        t_start = time.time()
        try:
            events = analyzer.process_video(
                video_source=video_file,
                sample_fps=args.fps,
                max_duration_sec=args.limit_sec,
                conf_threshold=args.conf,
                use_motion_filter=use_mf,
                min_motion_ratio=args.motion_thresh,
                cctv_id=cid
            )
            t_elapsed = time.time() - t_start
            total_events_found += len(events)
            print(f"⏱️ Waktu analisa video: {t_elapsed:.1f} detik | Total event: {len(events)}")
        except Exception as e:
            print(f"❌ Gagal memproses video {filename}: {e}")

    total_duration = time.time() - start_all
    avg_per_video = total_duration / max(1, len(files_to_process))

    print("\n==================== RINGKASAN PENGUJIAN NODE ====================")
    print(f"Total Video Diproses   : {len(files_to_process)} video")
    print(f"Total Kamera           : {total_cams} kamera")
    print(f"Total Event Terdeteksi : {total_events_found} event penting")
    print(f"Total Waktu Komputasi  : {total_duration:.1f} detik ({total_duration/60:.1f} menit)")
    print(f"Rata-rata per Video    : {avg_per_video:.1f} detik / file 15-menit")
    print(f"Hasil di Master Web    : Buka menu 'Intelligent (AI)' di {args.master_url}")
    print("==================================================================\n")


if __name__ == '__main__':
    main()
