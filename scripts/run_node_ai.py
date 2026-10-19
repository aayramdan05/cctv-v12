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

# 1. Kunci thread CPU library C-runtime (OpenMP / BLAS / MKL) agar AI hanya memakai 1 Core CPU
os.environ['OMP_NUM_THREADS'] = '1'
os.environ['OPENBLAS_NUM_THREADS'] = '1'
os.environ['MKL_NUM_THREADS'] = '1'
os.environ['VECLIB_MAXIMUM_THREADS'] = '1'
os.environ['NUMEXPR_NUM_THREADS'] = '1'

# Prioritaskan CPU paling rendah (nice 15) di lingkungan Linux agar perekaman FFmpeg & streaming tidak terganggu
try:
    os.nice(15)
except Exception:
    pass

try:
    import torch
    torch.set_num_threads(1)
    if hasattr(torch, 'set_num_interop_threads'):
        torch.set_num_interop_threads(1)
except Exception:
    pass

try:
    import cv2
    cv2.setNumThreads(1)
except Exception:
    pass

import time
import glob
import argparse
from datetime import datetime
from dotenv import load_dotenv

# Load environment variable
load_dotenv('/home/aay/cctv-scripts/.env')
load_dotenv('.env')

# Pastikan import script lokal
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
if SCRIPT_DIR not in sys.path:
    sys.path.insert(0, SCRIPT_DIR)

from ai_analyzer import CctvAiAnalyzer, has_moov_atom


def find_recording_files(base_dir, date_str=None, cctv_id=None, latest_only=True):
    """
    Mencari file rekaman CCTV (.mp4) yang SUDAH SELESAI (memiliki moov atom).
    File yang sedang aktif direkam oleh FFmpeg diabaikan agar tidak memicu error 'moov atom not found'.
    Format path: {base_dir}/{YYYY-MM-DD}/cam_{id}_{timestamp}.mp4
    """
    if not os.path.exists(base_dir):
        print(f"❌ Direktori rekaman tidak ditemukan: {base_dir}")
        return []

    if date_str == 'today' or not date_str:
        date_str = datetime.now().strftime('%Y-%m-%d')

    target_folder = os.path.join(base_dir, date_str)
    if not os.path.exists(target_folder):
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

    # Urutkan file berdasarkan waktu modifikasi terbaru (terkini -> terlama)
    for cid in cams_files:
        cams_files[cid].sort(key=lambda x: os.path.getmtime(x), reverse=True)

    # Cari folder tanggal sebelumnya untuk fallback jika file hari ini masih aktif direkam
    prev_folders = sorted([d for d in glob.glob(os.path.join(base_dir, "*")) if os.path.isdir(d) and os.path.basename(d) < date_str], reverse=True)

    selected_files = []
    if latest_only:
        # Ambil hanya 1 rekaman paling baru per kamera yang SUDAH SELESAI
        for cid in sorted(cams_files.keys()):
            completed_file = None
            for f in cams_files[cid]:
                if has_moov_atom(f):
                    completed_file = f
                    break

            if completed_file:
                selected_files.append((cid, completed_file))
            else:
                # File hari ini untuk kamera ini masih in-progress, cari file rekaman selesai dari tanggal sebelumnya
                found_prev = False
                for prev_dir in prev_folders[:3]:
                    prev_pattern = os.path.join(prev_dir, f"cam_{cid}_*.mp4")
                    prev_files = sorted(glob.glob(prev_pattern), key=os.path.getmtime, reverse=True)
                    for pf in prev_files:
                        if has_moov_atom(pf):
                            selected_files.append((cid, pf))
                            found_prev = True
                            print(f"ℹ️ [CAM {cid}] Rekaman hari ini masih aktif direkam FFmpeg. Mengambil rekaman selesai dari {os.path.basename(prev_dir)}: {os.path.basename(pf)}")
                            break
                    if found_prev:
                        break

                if not found_prev:
                    print(f"⏳ [CAM {cid}] File {os.path.basename(cams_files[cid][0])} masih aktif direkam oleh FFmpeg (menunggu chunk 15m selesai).")
    else:
        # Ambil semua rekaman yang sudah selesai
        for cid in sorted(cams_files.keys()):
            for f in cams_files[cid]:
                if has_moov_atom(f):
                    selected_files.append((cid, f))

    return selected_files


def watch_and_process(analyzer, base_dir, sample_fps, conf, use_motion_filter, motion_thresh, limit_sec, imgsz=480, min_contour_area=150, max_age_hours=2.0):
    """
    Mode Watcher Real-Time Cerdas:
    - Memantau rekaman terbaru dari CCTV secara real-time.
    - Prioritas LIFO (Terbaru ke Terlama): Memproses chunk rekaman paling baru terlebih dahulu agar dashboard selalu update waktu terkini.
    - Otomatis melewati rekaman basi (> 2 jam yang lalu) jika terjadi antrean/backlog, agar sistem segera sinkron dengan waktu sekarang (LIVE).
    - Adaptif terhadap pergantian hari (midnight rollover).
    - Memproses 1 file terbaru per iterasi, lalu memindai ulang direktori agar file baru yang baru saja selesai langsung terdeteksi seketika.
    """
    print(f"\n👀 [WATCHER MODE AKTIF - REAL-TIME PRIORITY] Memantau rekaman di: {base_dir}")
    print(f"ℹ️ Batas umur rekaman: {max_age_hours:.1f} jam (rekaman lebih lama dilewati agar tetap real-time)")
    print("Tekan Ctrl+C untuk berhenti.\n")

    processed_files = set()
    last_cleaned_day = None

    while True:
        try:
            now = datetime.now()
            today_str = now.strftime('%Y-%m-%d')
            today_folder = os.path.join(base_dir, today_str)

            # Jika hari berganti, sinkronkan ke hari baru dan bersihkan memori set
            if last_cleaned_day != today_str:
                last_cleaned_day = today_str
                if len(processed_files) > 5000:
                    processed_files.clear()
                print(f"📅 [PERGANTIAN TANGGAL] Memantau folder rekaman hari ini: {today_str}")

            if not os.path.exists(today_folder):
                time.sleep(5)
                continue

            all_files = glob.glob(os.path.join(today_folder, "*.mp4"))
            if not all_files:
                time.sleep(5)
                continue

            # Saring file yang belum pernah diproses
            unprocessed = [f for f in all_files if f not in processed_files]
            if not unprocessed:
                time.sleep(3)
                continue

            # Prioritaskan rekaman TERBARU terlebih dahulu (LIFO - Real-Time First!)
            unprocessed.sort(key=lambda x: os.path.getmtime(x), reverse=True)

            cutoff_mtime = now.timestamp() - (max_age_hours * 3600)

            # Cari 1 file rekaman terbaru yang sudah selesai (memiliki moov atom)
            target_file = None
            for filepath in unprocessed:
                mtime = os.path.getmtime(filepath)
                # Jika file lebih tua dari batas maksimal (misal server sempat tertinggal berhari-hari),
                # lewati dan tandai selesai agar AI langsung lompat ke rekaman jam terkini
                if mtime < cutoff_mtime:
                    processed_files.add(filepath)
                    continue

                if has_moov_atom(filepath):
                    size_mb = os.path.getsize(filepath) / (1024 * 1024)
                    processed_files.add(filepath)

                    # Lewati cuplikan terlalu pendek (< 2MB)
                    if size_mb < 2.0:
                        continue

                    target_file = filepath
                    break

            if target_file is None:
                time.sleep(3)
                continue

            size_mb = os.path.getsize(target_file) / (1024 * 1024)
            print(f"\n🎬 [REKAMAN TERBARU SELESAI] {os.path.basename(target_file)} ({size_mb:.1f} MB)")

            # Ekstrak CCTV ID
            base = os.path.basename(target_file)
            parts = base.split('_')
            cam_id = int(parts[1]) if len(parts) >= 2 and parts[1].isdigit() else 1

            t0 = time.time()
            events = analyzer.process_video(
                video_source=target_file,
                sample_fps=sample_fps,
                max_duration_sec=limit_sec,
                conf_threshold=conf,
                use_motion_filter=use_motion_filter,
                min_motion_ratio=motion_thresh,
                cctv_id=cam_id,
                imgsz=imgsz,
                min_contour_area=min_contour_area
            )
            dur = time.time() - t0
            print(f"✅ Selesai analisa dalam {dur:.1f} detik. {len(events)} event dicatat ke Master.\n")
            time.sleep(1.0) # Jeda singkat sebelum re-scan file baru kamera lain

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
    parser.add_argument("--fps", type=float, default=1.0,
                        help="Sampling FPS (default: 1.0, contoh: 0.5 untuk 1 frame tiap 2 detik, 0.33 untuk 1 frame tiap 3 detik)")
    parser.add_argument("--imgsz", type=int, default=480,
                        help="Resolusi inferensi YOLO (default: 480 untuk CPU cepat, atau 640)")
    parser.add_argument("--conf", type=float, default=0.25,
                        help="Ambang batas deteksi confidence YOLO (default: 0.25)")
    parser.add_argument("--no-motion-filter", action="store_true",
                        help="Matikan fitur Motion Pre-filter")
    parser.add_argument("--motion-thresh", type=float, default=0.003,
                        help="Threshold gerakan pixel ratio (default: 0.003 / 0.3%%)")
    parser.add_argument("--motion-area", type=int, default=150,
                        help="Ambang batas ukuran gumpalan objek pixel area (default: 150 px, gunakan 250-400 untuk abaikan daun/angin)")
    parser.add_argument("--watch", action="store_true",
                        help="Jalankan dalam mode Watcher (otomatis analisa file baru saat selesai direkam)")
    parser.add_argument("--max-age-hours", type=float, default=2.0,
                        help="Batas umur maksimal file rekaman dalam mode watch (default: 2.0 jam). File lebih lama dilewati agar AI selalu real-time.")

    args = parser.parse_args()

    use_mf = not args.no_motion_filter

    print("=================================================================")
    print("   UNPAD CCTV AI ANALYZER - NODE ENGINE (42 KAMERA RUNNER)")
    print("=================================================================")
    print(f"Direktori Rekaman : {args.recordings_dir}")
    print(f"Master Laravel    : {args.master_url}")
    print(f"Sampling Rate     : {args.fps} FPS")
    print(f"Resolusi Inferensi: {args.imgsz}x{args.imgsz}")
    print(f"Motion Pre-Filter : {'AKTIF (Hemat CPU 70-85%)' if use_mf else 'NONAKTIF'}")
    print(f"Blob Min Area     : {args.motion_area} px")
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
            limit_sec=args.limit_sec,
            imgsz=args.imgsz,
            min_contour_area=args.motion_area,
            max_age_hours=args.max_age_hours
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
                cctv_id=cid,
                imgsz=args.imgsz,
                min_contour_area=args.motion_area
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
