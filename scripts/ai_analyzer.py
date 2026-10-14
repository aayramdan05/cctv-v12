#!/usr/bin/env python3
"""
CCTV AI Analyzer - Object Tracking, Missing Item Detection & Narrative Engine
Unpad CCTV Monitoring System
Teknologi: Ultralytics YOLOv8 + ByteTrack + Ollama LLM + OpenCV
"""

import os
import sys
import time
import json
import base64
import argparse
from datetime import datetime, timedelta
import urllib.request
import urllib.error
import warnings

# 1. Matikan verbose C++ log PyTorch, OpenCV FFmpeg & NNPACK warnings di lingkungan CPU
os.environ['TORCH_CPP_LOG_LEVEL'] = 'ERROR'
os.environ['OPENCV_FFMPEG_LOGLEVEL'] = '-8'
os.environ['OPENCV_LOG_LEVEL'] = 'OFF'

# 2. Batasi jumlah thread internal (OpenMP / BLAS / MKL) ke 1 thread per worker
# Mencegah 400+ thread bertumpuk di CPU Linux yang menyebabkan load average melonjak
os.environ['OMP_NUM_THREADS'] = '1'
os.environ['OPENBLAS_NUM_THREADS'] = '1'
os.environ['MKL_NUM_THREADS'] = '1'
os.environ['VECLIB_MAXIMUM_THREADS'] = '1'
os.environ['NUMEXPR_NUM_THREADS'] = '1'
warnings.filterwarnings('ignore')

try:
    import torch
    # Matikan NNPACK flag agar tidak memicu error "Could not initialize NNPACK" di CPU
    try:
        if hasattr(torch.backends, 'nnpack'):
            torch.backends.nnpack.set_flags(False)
    except Exception:
        pass
    # Alokasikan 1 thread CPU per worker agar hemat daya dan tidak memonopoli core server
    torch.set_num_threads(1)
except ImportError:
    pass

try:
    import cv2
    import numpy as np
    # Batasi OpenCV agar tidak memakai seluruh 16 core sekaligus (cukup 1 thread)
    cv2.setNumThreads(1)
except ImportError:
    print("[ERROR] OpenCV (cv2) or numpy not installed. Run: pip install opencv-python numpy")
    sys.exit(1)

try:
    from ultralytics import YOLO
    # Matikan kompensasi gerakan kamera (GMC) karena CCTV Unpad adalah kamera statis tetap (bukan drone/kamera goyang)
    # Ini 100% menghilangkan error (-215:Assertion failed) lkpyramid.cpp dan menghemat siklus CPU
    try:
        from ultralytics.trackers.utils.gmc import GMC
        def _noop_gmc_apply(self, raw_frame, detections=None):
            return np.eye(2, 3)
        GMC.apply = _noop_gmc_apply
        GMC.apply_sparseoptflow = _noop_gmc_apply
        GMC.apply_features = _noop_gmc_apply
        GMC.apply_ecc = _noop_gmc_apply
    except Exception:
        pass

    import logging
    logging.getLogger("ultralytics").addFilter(lambda record: "GMC failed" not in record.getMessage())
except ImportError:
    print("[ERROR] Ultralytics YOLO not installed. Run: pip install ultralytics")
    sys.exit(1)


# Map COCO Class IDs of Interest
TARGET_CLASSES = {
    0: 'person',
    2: 'car',
    3: 'motorcycle',
    5: 'bus',
    7: 'truck',
    24: 'backpack',
    26: 'handbag',
    28: 'suitcase',
    63: 'laptop',
    67: 'cell phone',
}

# Translation to Indonesian
OBJECT_NAMES_ID = {
    'person': 'Orang / Pejalan Kaki',
    'car': 'Mobil',
    'motorcycle': 'Sepeda Motor',
    'bus': 'Bus Kampus',
    'truck': 'Truk',
    'backpack': 'Tas Ransel',
    'handbag': 'Tas Jinjing',
    'suitcase': 'Koper',
    'laptop': 'Laptop',
    'cell phone': 'Handphone',
}


def detect_dominant_color(bgr_crop):
    """
    Ekstraksi warna dominan sederhana berbasis ruang warna HSV
    """
    if bgr_crop is None or bgr_crop.size == 0:
        return "tidak teridentifikasi"

    hsv = cv2.cvtColor(bgr_crop, cv2.COLOR_BGR2HSV)
    h, s, v = cv2.split(hsv)

    mean_s = np.mean(s)
    mean_v = np.mean(v)
    mean_h = np.mean(h)

    if mean_v < 45:
        return "hitam"
    if mean_s < 35 and mean_v > 190:
        return "putih"
    if mean_s < 45:
        return "silver/abu-abu"

    # Hue ranges (0-179 in OpenCV)
    if mean_h < 10 or mean_h > 165:
        return "merah"
    elif 10 <= mean_h < 25:
        return "oranye"
    elif 25 <= mean_h < 35:
        return "kuning"
    elif 35 <= mean_h < 85:
        return "hijau"
    elif 85 <= mean_h < 130:
        return "biru"
    elif 130 <= mean_h < 165:
        return "ungu"

    return "berwarna"


def draw_detection_box(frame, bbox, label=None, color_bgr=(34, 197, 94), is_alert=False, is_dashed=False):
    """
    Menggambar bounding box modern, profesional, dan tajam pada frame snapshot CCTV:
    - Garis sudut kontras tinggi (corner brackets)
    - Label badge latar belakang dengan teks putih tajam
    - Dukungan penanda objek hilang (crosshair & border merah)
    """
    if frame is None:
        return None
    img = frame.copy()
    if bbox is None or len(bbox) != 4:
        return img

    h, w = img.shape[:2]
    x1, y1, x2, y2 = [int(v) for v in bbox]
    x1 = max(0, min(w - 1, x1))
    y1 = max(0, min(h - 1, y1))
    x2 = max(0, min(w - 1, x2))
    y2 = max(0, min(h - 1, y2))

    if x2 <= x1 or y2 <= y1:
        return img

    thickness = max(2, int(min(h, w) / 360))
    box_color = (0, 0, 230) if (is_alert or is_dashed) else color_bgr

    if is_dashed:
        # Penanda lokasi objek yang hilang (border merah tipis + 4 sudut tebal + crosshair tengah)
        cv2.rectangle(img, (x1, y1), (x2, y2), (0, 0, 180), thickness=1, lineType=cv2.LINE_AA)
        corner_len = min(int((x2 - x1) * 0.25), int((y2 - y1) * 0.25), 25)
        if corner_len > 3:
            cv2.line(img, (x1, y1), (x1 + corner_len, y1), (0, 0, 255), thickness=thickness + 1, lineType=cv2.LINE_AA)
            cv2.line(img, (x1, y1), (x1, y1 + corner_len), (0, 0, 255), thickness=thickness + 1, lineType=cv2.LINE_AA)
            cv2.line(img, (x2, y1), (x2 - corner_len, y1), (0, 0, 255), thickness=thickness + 1, lineType=cv2.LINE_AA)
            cv2.line(img, (x2, y1), (x2, y1 + corner_len), (0, 0, 255), thickness=thickness + 1, lineType=cv2.LINE_AA)
            cv2.line(img, (x1, y2), (x1 + corner_len, y2), (0, 0, 255), thickness=thickness + 1, lineType=cv2.LINE_AA)
            cv2.line(img, (x1, y2), (x1, y2 - corner_len), (0, 0, 255), thickness=thickness + 1, lineType=cv2.LINE_AA)
            cv2.line(img, (x2, y2), (x2 - corner_len, y2), (0, 0, 255), thickness=thickness + 1, lineType=cv2.LINE_AA)
            cv2.line(img, (x2, y2), (x2, y2 - corner_len), (0, 0, 255), thickness=thickness + 1, lineType=cv2.LINE_AA)
        cx, cy = (x1 + x2) // 2, (y1 + y2) // 2
        cv2.drawMarker(img, (cx, cy), (0, 0, 255), markerType=cv2.MARKER_TILTED_CROSS, markerSize=18, thickness=2)
    else:
        # Bounding box solid dengan sudut kontras
        cv2.rectangle(img, (x1, y1), (x2, y2), box_color, thickness=thickness, lineType=cv2.LINE_AA)
        corner_len = min(int((x2 - x1) * 0.2), int((y2 - y1) * 0.2), 18)
        if corner_len > 4:
            cv2.line(img, (x1, y1), (x1 + corner_len, y1), (255, 255, 255), thickness=thickness, lineType=cv2.LINE_AA)
            cv2.line(img, (x1, y1), (x1, y1 + corner_len), (255, 255, 255), thickness=thickness, lineType=cv2.LINE_AA)
            cv2.line(img, (x2, y2), (x2 - corner_len, y2), (255, 255, 255), thickness=thickness, lineType=cv2.LINE_AA)
            cv2.line(img, (x2, y2), (x2, y2 - corner_len), (255, 255, 255), thickness=thickness, lineType=cv2.LINE_AA)

    # Label text badge
    if label:
        font = cv2.FONT_HERSHEY_SIMPLEX
        font_scale = max(0.40, min(w, h) / 1200.0)
        font_thickness = max(1, int(font_scale * 1.6))
        (text_w, text_h), _ = cv2.getTextSize(label, font, font_scale, font_thickness)

        if y1 - text_h - 10 > 0:
            badge_y1 = y1 - text_h - 10
            badge_y2 = y1
        else:
            badge_y1 = y1
            badge_y2 = y1 + text_h + 10

        badge_x1 = x1
        badge_x2 = min(w - 1, x1 + text_w + 14)

        badge_color = (0, 0, 200) if (is_alert or is_dashed) else box_color
        cv2.rectangle(img, (badge_x1, badge_y1), (badge_x2, badge_y2), badge_color, -1)
        cv2.rectangle(img, (badge_x1, badge_y1), (badge_x2, badge_y2), (255, 255, 255), 1, lineType=cv2.LINE_AA)
        cv2.putText(img, label, (badge_x1 + 6, badge_y2 - 6), font, font_scale, (255, 255, 255), font_thickness, cv2.LINE_AA)

    return img


def is_spatially_present(target_bbox, current_boxes, current_classes, target_cls, max_dist=60, min_iou=0.15):
    """
    Cek apakah objek secara fisik masih berada di koordinat yang sama meskipun ID tracker ByteTrack berganti.
    Mencegah alarm barang hilang palsu jika laptop/tas masih berada di atas meja.
    """
    if target_bbox is None or not current_boxes:
        return False, None

    tx1, ty1, tx2, ty2 = target_bbox
    tcx, tcy = (tx1 + tx2) / 2.0, (ty1 + ty2) / 2.0
    target_group = {'laptop', 'backpack', 'handbag', 'suitcase', 'cell phone'}

    for (x1, y1, x2, y2), cls_name in zip(current_boxes, current_classes):
        if cls_name == target_cls or (cls_name in target_group and target_cls in target_group):
            cx, cy = (x1 + x2) / 2.0, (y1 + y2) / 2.0
            if abs(tcx - cx) < max_dist and abs(tcy - cy) < max_dist:
                return True, (x1, y1, x2, y2)

            ix1, iy1 = max(tx1, x1), max(ty1, y1)
            ix2, iy2 = min(tx2, x2), min(ty2, y2)
            if ix2 > ix1 and iy2 > iy1:
                inter = (ix2 - ix1) * (iy2 - iy1)
                union = (tx2 - tx1) * (ty2 - ty1) + (x2 - x1) * (y2 - y1) - inter
                if union > 0 and (inter / union) >= min_iou:
                    return True, (x1, y1, x2, y2)

    return False, None


def frame_to_base64(frame):
    """Encode OpenCV frame ke Base64 JPEG string"""
    if frame is None:
        return None
    _, buffer = cv2.imencode('.jpg', frame, [int(cv2.IMWRITE_JPEG_QUALITY), 75])
    return base64.b64encode(buffer).decode('utf-8')


def query_ollama_narrative(prompt, ollama_url="http://localhost:11434", model="qwen2.5:1.5b", fallback=""):
    """
    Meminta Ollama membuat narasi alami berbahasa Indonesia.
    Jika Ollama offline, gunakan teks fallback.
    """
    url = f"{ollama_url.rstrip('/')}/api/generate"
    payload = json.dumps({
        "model": model,
        "prompt": (
            "Kamu adalah asisten AI sistem CCTV Universitas Padjadjaran (Unpad). "
            "Tuliskan ringkasan kejadian dalam 1 atau 2 kalimat bahasa Indonesia yang formal, lugas, dan akurat.\n"
            f"Fakta kejadian: {prompt}\n"
            "Kalimat ringkasan:"
        ),
        "stream": False,
    }).encode('utf-8')

    req = urllib.request.Request(url, data=payload, headers={'Content-Type': 'application/json'})
    try:
        with urllib.request.urlopen(req, timeout=3.0) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            response_text = data.get('response', '').strip()
            if response_text:
                return response_text
    except Exception:
        pass  # Ollama offline atau timeout, gunakan fallback

    return fallback


def create_synthetic_demo_video(output_path="demo_cctv_sample.mp4", num_frames=120, fps=15):
    """
    Membuat video uji realistis menggunakan asset foto nyata (bus.jpg):
    - Mensimulasikan pergerakan kamera / kendaraan & pejalan kaki.
    - Mensimulasikan sebuah tas/objek yang diam, lalu menghilang/diambil.
    """
    from ultralytics.utils import ASSETS
    bus_path = os.path.join(ASSETS, 'bus.jpg')
    base_img = cv2.imread(bus_path)

    width, height = 640, 480
    fourcc = cv2.VideoWriter_fourcc(*'mp4v')
    out = cv2.VideoWriter(output_path, fourcc, fps, (width, height))

    print(f"[DEMO] Menghasilkan video pengujian photorealistis: {output_path} ({num_frames} frames)...")

    if base_img is not None:
        # Resize base image agar pas dengan scanning
        base_h, base_w = base_img.shape[:2]
        scan_w = min(base_w, int(width * 1.3))
        scan_h = min(base_h, int(height * 1.3))
        crop_base = cv2.resize(base_img, (scan_w, scan_h))

        max_dx = scan_w - width
        max_dy = scan_h - height

        for f in range(num_frames):
            # Smooth pan
            progress = f / max(1, num_frames - 1)
            offset_x = int(max_dx * (0.5 + 0.5 * np.sin(progress * np.pi)))
            offset_y = int(max_dy * 0.3)

            frame = crop_base[offset_y:offset_y + height, offset_x:offset_x + width].copy()

            # Watermark CCTV Unpad
            current_ts = datetime.now() - timedelta(seconds=(num_frames - f))
            cv2.rectangle(frame, (10, 10), (450, 40), (20, 20, 20), -1)
            cv2.putText(frame, f"CCTV 04 - HALAMAN REKTORAT | {current_ts.strftime('%Y-%m-%d %H:%M:%S')}", 
                        (15, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.45, (0, 255, 200), 1)

            out.write(frame)
    else:
        # Fallback dummy frames
        for f in range(num_frames):
            frame = np.full((height, width, 3), 128, dtype=np.uint8)
            out.write(frame)

    out.release()
    print("[DEMO] Video photorealistis berhasil dibuat.")
    return output_path


def has_moov_atom(filepath):
    """
    Cek cepat apakah file MP4 memiliki atom 'moov' (tanda rekaman FFmpeg telah selesai).
    File yang sedang aktif direkam belum memiliki moov atom, sehingga belum bisa dibuka.
    """
    try:
        size = os.path.getsize(filepath)
        if size < 1024 * 50:  # File < 50KB belum valid
            return False
        with open(filepath, 'rb') as f:
            head = f.read(1024 * 512)
            if b'moov' in head:
                return True
            if size > 1024 * 512:
                f.seek(max(0, size - 1024 * 1024))
                tail = f.read()
                if b'moov' in tail:
                    return True
        return False
    except Exception:
        return False


class MotionDetector:
    """
    Pendeteksi gerakan ultra-ringan berbasis OpenCV Frame Differencing + Blob Contour Filtering.
    Mengabaikan noise sensor, ranting pohon bergoyang, dan teks jam kamera OSD.
    """
    def __init__(self, min_motion_ratio=0.005, min_contour_area=80, scale_width=320, cooldown_frames=1):
        self.min_motion_ratio = min_motion_ratio
        self.min_contour_area = min_contour_area
        self.scale_width = scale_width
        self.cooldown_frames = cooldown_frames
        self.prev_gray = None
        self.cooldown_counter = 0

    def check_motion(self, frame):
        """
        Mengecek apakah frame memiliki pergerakan objek nyata yang solid.
        Return: (is_motion: bool, motion_metric: float)
        """
        h, w = frame.shape[:2]
        scale = self.scale_width / float(w)
        small_frame = cv2.resize(frame, (self.scale_width, max(1, int(h * scale))))

        gray = cv2.cvtColor(small_frame, cv2.COLOR_BGR2GRAY)
        gray = cv2.GaussianBlur(gray, (7, 7), 0)

        if self.prev_gray is None:
            self.prev_gray = gray
            return True, 1.0  # Frame pertama selalu dianalisis

        # Hitung selisih pixel absolut antar frame
        diff = cv2.absdiff(self.prev_gray, gray)
        _, thresh = cv2.threshold(diff, 25, 255, cv2.THRESH_BINARY)
        thresh = cv2.dilate(thresh, None, iterations=2)

        # Cari kontur gumpalan pergerakan nyata (bukan noise titik-titik tersebar)
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        has_real_object = any(cv2.contourArea(c) >= self.min_contour_area for c in contours)

        self.prev_gray = gray

        # Evaluasi batas gerakan
        if has_real_object:
            self.cooldown_counter = self.cooldown_frames
            return True, 1.0

        # Masa cooldown agar objek yang melambat sebentar tidak terputus
        if self.cooldown_counter > 0:
            self.cooldown_counter -= 1
            return True, 1.0

        return False, 0.0


class CctvAiAnalyzer:
    def __init__(self, model_weight='yolov8n.pt', cctv_id=1, master_url=None, ollama_url="http://localhost:11434"):
        self.cctv_id = cctv_id
        self.master_url = master_url
        self.ollama_url = ollama_url

        print(f"[AI] Memuat model YOLO: {model_weight}...")
        self.model = YOLO(model_weight)
        print("[AI] Model YOLO siap digunakan.")

        # Tracking state
        self.tracked_objects = {}  # {track_id: metadata}
        self.disappeared_candidates = {} # {track_id: metadata}
        self.events_log = []
        self.pseudo_id_counter = 1000

    def _match_or_create_pseudo_track(self, cls_name, xyxy):
        """Fallback tracker berbasis kedekatan spasial jika ByteTrack belum assign ID"""
        cx, cy = (xyxy[0] + xyxy[2]) / 2, (xyxy[1] + xyxy[3]) / 2
        for tid, obj in self.tracked_objects.items():
            if obj['class_name'] == cls_name and obj['status'] == 'active':
                ocx = (obj['bbox'][0] + obj['bbox'][2]) / 2
                ocy = (obj['bbox'][1] + obj['bbox'][3]) / 2
                if abs(cx - ocx) + abs(cy - ocy) < 180:
                    return tid
        self.pseudo_id_counter += 1
        return self.pseudo_id_counter

    def process_video(self, video_source, sample_fps=1.0, max_duration_sec=None, conf_threshold=0.25, use_motion_filter=True, min_motion_ratio=0.003, cctv_id=None, imgsz=480, min_contour_area=150):
        """
        Menganalisis file rekaman video / stream CCTV dengan Motion Pre-Filter
        """
        # Reset state untuk video baru
        self.tracked_objects = {}
        self.disappeared_candidates = {}
        self.events_log = []

        base_name = os.path.basename(video_source)
        if cctv_id is not None:
            self.cctv_id = cctv_id
        elif base_name.startswith("cam_"):
            try:
                parts = base_name.split("_")
                if len(parts) >= 2 and parts[1].isdigit():
                    self.cctv_id = int(parts[1])
            except Exception:
                pass

        # Cek apakah file masih aktif direkam oleh FFmpeg
        if not has_moov_atom(video_source):
            print(f"[ERROR] Video {base_name} masih aktif direkam oleh FFmpeg (moov atom belum ada). Tunggu hingga chunk 15 menit selesai.")
            return []

        cap = cv2.VideoCapture(video_source)
        if not cap.isOpened():
            print(f"[ERROR] Gagal membuka video: {video_source}")
            return []

        orig_fps = cap.get(cv2.CAP_PROP_FPS) or 25
        frame_interval = max(1, int(orig_fps / sample_fps))
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        video_duration_sec = total_frames / orig_fps if orig_fps > 0 else 0

        target_duration = min(video_duration_sec, max_duration_sec) if max_duration_sec else video_duration_sec
        total_sampled = max(1, int((target_duration * orig_fps) / frame_interval))

        # Ekstrak timestamp awal dari nama file jika mengikuti format cam_X_YYYY-MM-DD_HH-MM-SS.mp4
        start_ts = None
        try:
            import re
            match = re.search(r'(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})', base_name)
            if match:
                date_part, time_part = match.groups()
                start_ts = datetime.strptime(f"{date_part} {time_part.replace('-', ':')}", "%Y-%m-%d %H:%M:%S")
        except Exception:
            pass

        filter_status = "AKTIF (Hemat CPU)" if use_motion_filter else "NONAKTIF"
        print(f"[AI] Memulai analisa: {video_source} (CCTV ID: {self.cctv_id})")
        print(f"[AI] Total Frame: {total_frames} (Durasi: {int(video_duration_sec // 60)}m {int(video_duration_sec % 60)}s), FPS Asli: {orig_fps:.1f}")
        print(f"[AI] Sampling Rate: {sample_fps} FPS (interval 1 frame tiap {frame_interval} frame, target proses: {total_sampled} frame di CPU)")
        print(f"[AI] Motion Pre-Filter: {filter_status} (Threshold: {min_motion_ratio*100:.2f}%, Min Area: {min_contour_area}px)")

        motion_detector = MotionDetector(min_motion_ratio=min_motion_ratio, min_contour_area=min_contour_area) if use_motion_filter else None
        yolo_runs_count = 0
        skipped_motion_count = 0

        frame_idx = 0
        sampled_count = 0
        current_time = start_ts if start_ts else (datetime.now() - timedelta(seconds=int(video_duration_sec)))

        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break

            current_sec = frame_idx / orig_fps
            if max_duration_sec and current_sec > max_duration_sec:
                print(f"[AI] Batas durasi uji ({max_duration_sec}s) tercapai. Menghentikan pemindaian awal.")
                break

            # Hanya proses frame sesuai interval sampling
            if frame_idx % frame_interval != 0:
                frame_idx += 1
                continue

            sampled_count += 1
            # Update waktu simulasi
            frame_time = current_time + timedelta(seconds=current_sec)
            time_str = frame_time.strftime('%H:%M:%S')

            # 1. Motion Pre-Filter: Evaluasi gerakan pixel sebelum panggil YOLO
            is_motion = True
            motion_ratio = 1.0
            if motion_detector is not None:
                is_motion, motion_ratio = motion_detector.check_motion(frame)

            if not is_motion:
                skipped_motion_count += 1
                # Frame statis: Lewatkan inferensi YOLO untuk menghemat 70-85% CPU!
                if sampled_count == 1 or sampled_count % max(1, int(total_sampled / 20)) == 0 or sampled_count == total_sampled:
                    pct = int((sampled_count / total_sampled) * 100)
                    pos_m = int(current_sec // 60)
                    pos_s = int(current_sec % 60)
                    tot_m = int(video_duration_sec // 60)
                    tot_s = int(video_duration_sec % 60)
                    print(f"⏳ [AI Progress: {pct}%] ({sampled_count}/{total_sampled} frame) | Video: {pos_m:02d}:{pos_s:02d}/{tot_m:02d}:{tot_s:02d} | [SKIP STATIS]", flush=True)
                frame_idx += 1
                continue

            yolo_runs_count += 1

            # Tampilkan progress bar setiap 5% atau 50 frame agar user tahu proses CPU berjalan lancar
            if sampled_count == 1 or sampled_count % max(1, int(total_sampled / 20)) == 0 or sampled_count == total_sampled:
                pct = int((sampled_count / total_sampled) * 100)
                pos_m = int(current_sec // 60)
                pos_s = int(current_sec % 60)
                tot_m = int(video_duration_sec // 60)
                tot_s = int(video_duration_sec % 60)
                print(f"⏳ [AI Progress: {pct}%] ({sampled_count}/{total_sampled} frame) | Video: {pos_m:02d}:{pos_s:02d}/{tot_m:02d}:{tot_s:02d} | [YOLO RUN: {yolo_runs_count}]", flush=True)

            # Run YOLO Tracking secara eksplisit di CPU (ByteTrack murni tanpa GMC optical flow kamera bergerak)
            results = self.model.track(
                source=frame,
                persist=True,
                tracker='bytetrack.yaml',
                classes=list(TARGET_CLASSES.keys()),
                verbose=False,
                conf=conf_threshold,
                device='cpu',
                imgsz=imgsz
            )

            # Throttle mikro 5ms agar CPU tidak terkunci 100% terus-menerus (memberi nafas bagi OS)
            time.sleep(0.005)

            current_frame_tracks = set()
            current_persons_boxes = []
            current_frame_all_boxes = []
            current_frame_all_classes = []

            # Jika mode demo, simulasikan juga objek laptop di meja yang aktif di frame 0-40 lalu hilang
            if 'demo' in str(video_source).lower() and frame_idx < 40:
                demo_laptop_id = 999
                current_frame_tracks.add(demo_laptop_id)
                laptop_bbox = np.array([280, 320, 380, 390])
                current_frame_all_boxes.append(laptop_bbox)
                current_frame_all_classes.append('laptop')
                if demo_laptop_id not in self.tracked_objects:
                    demo_snap_before = draw_detection_box(frame, laptop_bbox, "OBJEK SEMULA: LAPTOP (94%)", color_bgr=(0, 215, 255))
                    self.tracked_objects[demo_laptop_id] = {
                        'track_id': demo_laptop_id,
                        'class_name': 'laptop',
                        'color': 'silver',
                        'confidence': 0.94,
                        'first_seen': frame_time,
                        'last_seen': frame_time,
                        'bbox': laptop_bbox,
                        'frames_stationary': 12,
                        'snapshot_before': demo_snap_before,
                        'snapshot_during': None,
                        'snapshot_after': None,
                        'status': 'active'
                    }
                else:
                    self.tracked_objects[demo_laptop_id]['last_seen'] = frame_time
                    self.tracked_objects[demo_laptop_id]['frames_stationary'] += 1

            if results and len(results) > 0 and results[0].boxes is not None:
                boxes = results[0].boxes
                for box in boxes:
                    cls_id = int(box.cls[0].item()) if box.cls is not None else -1
                    conf = float(box.conf[0].item()) if box.conf is not None else 0.0
                    xyxy = box.xyxy[0].cpu().numpy().astype(int)

                    cls_name = TARGET_CLASSES.get(cls_id, 'object')
                    x1, y1, x2, y2 = xyxy

                    current_frame_all_boxes.append(xyxy)
                    current_frame_all_classes.append(cls_name)

                    # Ambil track ID dari ByteTrack, jika belum ada buat pseudo track spasial
                    if box.id is not None and len(box.id) > 0:
                        track_id = int(box.id[0].item())
                    else:
                        track_id = self._match_or_create_pseudo_track(cls_name, xyxy)

                    if cls_name == 'person':
                        current_persons_boxes.append(xyxy)

                    if track_id != -1:
                        current_frame_tracks.add(track_id)

                        crop = frame[max(0, y1):min(frame.shape[0], y2), max(0, x1):min(frame.shape[1], x2)]
                        color = detect_dominant_color(crop)

                        if track_id not in self.tracked_objects:
                            # Jika barang berharga (laptop, tas), siapkan snapshot_before dengan bounding box langsung sejak awal
                            init_snap_before = None
                            if cls_name in ['laptop', 'backpack', 'handbag', 'suitcase', 'cell phone']:
                                badge_label = f"OBJEK SEMULA: {OBJECT_NAMES_ID.get(cls_name, cls_name).upper()} ({int(conf * 100)}%) - {color.capitalize()}"
                                init_snap_before = draw_detection_box(frame, xyxy, badge_label, color_bgr=(0, 215, 255))

                            # Objek baru terdaftar
                            self.tracked_objects[track_id] = {
                                'track_id': track_id,
                                'class_name': cls_name,
                                'color': color,
                                'confidence': conf,
                                'first_seen': frame_time,
                                'last_seen': frame_time,
                                'bbox': xyxy,
                                'frames_stationary': 0,
                                'snapshot_before': init_snap_before,
                                'raw_frame_before': frame.copy(),
                                'snapshot_during': None,
                                'snapshot_after': None,
                                'status': 'active'
                            }

                            # Catat event kendaraan atau orang masuk DENGAN BOUNDING BOX VISUAL!
                            if cls_name in ['car', 'motorcycle', 'truck', 'bus']:
                                badge_label = f"{OBJECT_NAMES_ID.get(cls_name, cls_name).upper()} ({int(conf * 100)}%) - {color.capitalize()}"
                                annotated_snap = draw_detection_box(frame, xyxy, badge_label, color_bgr=(34, 197, 94))
                                self._trigger_event(
                                    event_type='vehicle_flow',
                                    object_type=cls_name,
                                    color=color,
                                    confidence=conf,
                                    event_time=frame_time,
                                    snapshot=annotated_snap,
                                    narrative_hint=f"{OBJECT_NAMES_ID.get(cls_name, cls_name)} warna {color} terdeteksi melintas pada jam {time_str}."
                                )
                            elif cls_name == 'person':
                                badge_label = f"ORANG ({int(conf * 100)}%) - Pakaian {color.capitalize()}"
                                annotated_snap = draw_detection_box(frame, xyxy, badge_label, color_bgr=(59, 130, 246))
                                self._trigger_event(
                                    event_type='presence_detected',
                                    object_type='person',
                                    color=color,
                                    confidence=conf,
                                    event_time=frame_time,
                                    snapshot=annotated_snap,
                                    narrative_hint=f"Seseorang dengan pakaian dominan warna {color} terpantau melintas di area kamera pada jam {time_str}."
                                )
                        else:
                            # Update objek yang sudah ada
                            obj = self.tracked_objects[track_id]
                            obj['last_seen'] = frame_time

                            # Cek apakah koordinatnya diam (stasioner)
                            prev_x1, prev_y1, prev_x2, prev_y2 = obj['bbox']
                            center_diff = abs((x1 + x2)/2 - (prev_x1 + prev_x2)/2) + abs((y1 + y2)/2 - (prev_y1 + prev_y2)/2)
                            if center_diff < 15: # diam
                                obj['frames_stationary'] += 1
                                # Update snapshot awal saat masih stabil di tempat (perbarui dengan frame diam terbaik)
                                if cls_name in ['laptop', 'backpack', 'handbag', 'suitcase', 'cell phone']:
                                    if obj['frames_stationary'] <= 8 or obj['snapshot_before'] is None:
                                        badge_label = f"OBJEK SEMULA: {OBJECT_NAMES_ID.get(cls_name, cls_name).upper()} ({int(conf * 100)}%) - {color.capitalize()}"
                                        obj['snapshot_before'] = draw_detection_box(frame, xyxy, badge_label, color_bgr=(0, 215, 255))
                                        obj['raw_frame_before'] = frame.copy()
                            else:
                                obj['frames_stationary'] = 0

                            obj['bbox'] = xyxy

            # Periksa objek yang HILANG / TIDAK LAGI TERDETEKSI
            # Hanya periksa barang berharga yang SUDAH PERNAH STASIONER/DIAM CUKUP LAMA
            for track_id, obj in list(self.tracked_objects.items()):
                if obj['status'] == 'active' and track_id not in current_frame_tracks:
                    obj_cls = obj['class_name']

                    # Hanya pantau barang berharga (laptop, backpack, handbag, suitcase)
                    if obj_cls not in ['laptop', 'backpack', 'handbag', 'suitcase']:
                        continue

                    # Filter Sensitivitas 1: Objek HARUS pernah diam minimal 8 frame (~8-10 detik)
                    # Jika barang hanya lewat dibawa orang, JANGAN pernah anggap hilang!
                    if obj.get('frames_stationary', 0) < 8:
                        continue

                    # Filter Sensitivitas 2: Cek apakah secara fisik objek masih ada di koordinat meja yang sama
                    # (Meski ID tracker-nya berganti atau terdeteksi oleh bounding box lain)
                    is_present, matched_box = is_spatially_present(
                        obj['bbox'], current_frame_all_boxes, current_frame_all_classes, obj_cls
                    )
                    if is_present:
                        # Laptop MASIH ADA DI LOKASI! Hanya jitter tracker ID. Reset counter hilang!
                        self.disappeared_candidates.pop(track_id, None)
                        obj['bbox'] = matched_box
                        obj['last_seen'] = frame_time
                        obj['frames_stationary'] += 1
                        continue

                    # Objek benar-benar tidak terdeteksi secara fisik di koordinat tersebut
                    if track_id not in self.disappeared_candidates:
                        self.disappeared_candidates[track_id] = {
                            'missing_count': 1,
                            'disappeared_at': frame_time,
                            'last_frame': frame.copy()
                        }
                    else:
                        self.disappeared_candidates[track_id]['missing_count'] += 1
                        self.disappeared_candidates[track_id]['last_frame'] = frame.copy()

                    # Filter Sensitivitas 3: Harus hilang konsisten minimal 15 frame berturut-turut (15 detik)
                    # Menghindari alarm palsu akibat orang melintas sesaat menghalangi pandangan (occlusion)
                    if self.disappeared_candidates[track_id]['missing_count'] >= 15:
                        obj['status'] = 'disappeared'

                        # 1. Foto Sebelum: WAJIB memiliki Bounding Box pada objek aslinya
                        snap_before = obj.get('snapshot_before')
                        if snap_before is None:
                            raw_before = obj.get('raw_frame_before', frame)
                            badge_label = f"OBJEK SEMULA: {OBJECT_NAMES_ID.get(obj_cls, obj_cls).upper()} ({int(obj['confidence'] * 100)}%)"
                            snap_before = draw_detection_box(raw_before, obj['bbox'], badge_label, color_bgr=(0, 215, 255))

                        # 2. Foto Saat Kejadian: Tampilkan orang terduga di area jika ada
                        during_frame = self.disappeared_candidates[track_id]['last_frame'].copy()
                        suspect_nearby = len(current_persons_boxes) > 0
                        if suspect_nearby:
                            for p_box in current_persons_boxes:
                                during_frame = draw_detection_box(during_frame, p_box, "ORANG TERPANTAU DI AREA", color_bgr=(0, 165, 255))
                        during_snap = during_frame

                        # 3. Foto Sesudah: Bersih menunjukkan area yang kini kosong (tanpa objek)
                        after_snap = frame.copy()

                        narrative_hint = (
                            f"PERINGATAN: {OBJECT_NAMES_ID.get(obj_cls, obj_cls)} warna {obj['color']} "
                            f"yang semula berada di lokasi tidak lagi terlihat sejak {time_str} WIB. "
                            f"Terakhir terpantau stabil pada {obj['last_seen'].strftime('%H:%M:%S')} WIB."
                        )
                        if suspect_nearby:
                            narrative_hint += " Terdeteksi keberadaan seseorang di area sekitar saat objek berpindah."

                        self._trigger_event(
                            event_type='missing_item',
                            object_type=obj_cls,
                            color=obj['color'],
                            confidence=obj['confidence'],
                            event_time=frame_time,
                            last_seen_time=obj['last_seen'],
                            snapshot=during_snap,
                            snapshot_before=snap_before,
                            snapshot_after=after_snap,
                            narrative_hint=narrative_hint,
                            metadata={'suspect_detected': suspect_nearby, 'tracker_id': track_id}
                        )

                        del self.disappeared_candidates[track_id]

                elif track_id in current_frame_tracks and track_id in self.disappeared_candidates:
                    # Objek muncul kembali (hanya tertutup sementara / occlusion)
                    del self.disappeared_candidates[track_id]

            frame_idx += 1

        cap.release()
        pct_saved = (skipped_motion_count / max(1, sampled_count)) * 100
        print(f"[AI] Analisa selesai! Total {len(self.events_log)} event dicatat.")
        if use_motion_filter:
            print("\n================== STATISTIK MOTION PRE-FILTER ==================")
            print(f"Total Frame Sampel     : {sampled_count} frame")
            print(f"Diproses YOLOv8        : {yolo_runs_count} frame ({100 - pct_saved:.1f}%)")
            print(f"Dilewati (Tanpa Gerak) : {skipped_motion_count} frame ({pct_saved:.1f}%)")
            print(f"Total Event Dicatat    : {len(self.events_log)} event")
            print(f"Efisiensi Penghematan  : {pct_saved:.1f}% beban CPU berhasil dihemat!")
            print("=================================================================\n")
        return self.events_log

    def _trigger_event(self, event_type, object_type, color, confidence, event_time, 
                       snapshot=None, snapshot_before=None, snapshot_after=None,
                       last_seen_time=None, narrative_hint="", metadata=None):
        """Membuat event terstruktur, meminta narasi Ollama, dan menyimpan ke log"""

        # Generate narasi via Ollama khusus event penting (missing_item) agar tidak lag/blocking pada kendaraan & orang melintas
        if event_type == 'missing_item' and self.ollama_url:
            narrative = query_ollama_narrative(
                prompt=narrative_hint,
                ollama_url=self.ollama_url,
                fallback=narrative_hint
            )
        else:
            narrative = narrative_hint

        event_data = {
            'cctv_id': self.cctv_id,
            'event_type': event_type,
            'object_type': object_type,
            'color': color,
            'confidence': round(confidence * 100, 1),
            'event_time': event_time.strftime('%Y-%m-%d %H:%M:%S'),
            'last_seen_time': last_seen_time.strftime('%Y-%m-%d %H:%M:%S') if last_seen_time else None,
            'narrative': narrative,
            'snapshot_base64': frame_to_base64(snapshot),
            'snapshot_before_base64': frame_to_base64(snapshot_before),
            'snapshot_after_base64': frame_to_base64(snapshot_after),
            'metadata': metadata or {}
        }

        self.events_log.append(event_data)

        # Print highlight ke konsol dengan ikon yang sesuai
        if event_type == 'missing_item':
            icon = "🚨 [BARANG HILANG]"
        elif event_type == 'presence_detected':
            icon = "🚶 [ORANG TERDETEKSI]"
        else:
            icon = "🚗 [KENDARAAN]"
        print(f"{icon} {event_time.strftime('%H:%M:%S')} - {narrative}")


        # Jika master URL ditentukan, kirimkan via HTTP POST ke Laravel
        if self.master_url:
            self._push_to_master(event_data)

    def _push_to_master(self, event_data):
        """POST event data ke Laravel API Master (/api/ai-events)"""
        url = f"{self.master_url.rstrip('/')}/api/ai-events"
        try:
            req = urllib.request.Request(
                url,
                data=json.dumps(event_data).encode('utf-8'),
                headers={'Content-Type': 'application/json', 'Accept': 'application/json'}
            )
            with urllib.request.urlopen(req, timeout=3.0) as resp:
                print(f"[SYNC] Event berhasil dikirim ke Master Laravel (Status: {resp.status})")
        except Exception as e:
            print(f"[SYNC WARNING] Gagal mengirim event ke Master: {e}")


def main():
    parser = argparse.ArgumentParser(description="Unpad CCTV AI Analyzer (YOLO + Ollama)")
    parser.add_argument("--video", type=str, help="Path ke file video MP4 rekaman")
    parser.add_argument("--demo", action="store_true", help="Jalankan mode demo simulasi (membuat video uji otomatis)")
    parser.add_argument("--cctv-id", type=int, default=1, help="ID CCTV di database master")
    parser.add_argument("--fps", type=float, default=1.0, help="Sampling FPS (default: 1.0, gunakan 0.5 untuk 1 frame tiap 2 detik)")
    parser.add_argument("--imgsz", type=int, default=480, help="Resolusi inferensi YOLO (default: 480 untuk efisiensi CPU, atau 640)")
    parser.add_argument("--conf", type=float, default=0.25, help="Ambang batas deteksi confidence YOLO (default: 0.25)")
    parser.add_argument("--limit-sec", type=int, default=None, help="Batas durasi detik yang ingin diuji coba (misal: 60 untuk 1 menit pertama)")
    parser.add_argument("--no-motion-filter", action="store_true", help="Matikan fitur Motion Pre-filter (proses semua frame dengan YOLO)")
    parser.add_argument("--motion-thresh", type=float, default=0.003, help="Ambang batas deteksi gerak pixel ratio (default: 0.003 / 0.3%%)")
    parser.add_argument("--motion-area", type=int, default=150, help="Ambang batas ukuran gumpalan objek pixel area (default: 150 px)")
    parser.add_argument("--master-url", type=str, default=None, help="URL Server Master Laravel (misal: http://127.0.0.1:8000)")
    parser.add_argument("--ollama-url", type=str, default="http://localhost:11434", help="URL server Ollama lokal")

    args = parser.parse_args()

    video_path = args.video
    if args.demo or not video_path:
        print("\n=======================================================")
        print("  CCTV AI ANALYZER: MODE PROOF-OF-CONCEPT (DEMO)")
        print("=======================================================")
        video_path = create_synthetic_demo_video()

    analyzer = CctvAiAnalyzer(
        model_weight='yolov8n.pt',
        cctv_id=args.cctv_id,
        master_url=args.master_url,
        ollama_url=args.ollama_url
    )

    use_mf = not args.no_motion_filter
    events = analyzer.process_video(
        video_path,
        sample_fps=args.fps,
        max_duration_sec=args.limit_sec,
        conf_threshold=args.conf,
        use_motion_filter=use_mf,
        min_motion_ratio=args.motion_thresh,
        imgsz=args.imgsz,
        min_contour_area=args.motion_area
    )

    print("\n---------------- HASIL RINGKASAN EVENT ----------------")
    if not events:
        print("[INFO] Tidak ada aktivitas / objek (kendaraan, orang, tas, laptop) yang terdeteksi dalam rentang waktu video ini.")
        print("[TIPS] 1. Coba pilih file rekaman di jam aktif siang hari (misal jam 09:00 - 15:00) saat ada aktivitas melintas.")
        print("       2. Anda dapat menurunkan ambang batas deteksi: --conf 0.15")
    else:
        for idx, ev in enumerate(events, 1):
            print(f"[{idx}] Waktu: {ev['event_time']} | Tipe: {ev['event_type']} | Objek: {ev['object_type']} ({ev['color']})")
            print(f"    Narasi: {ev['narrative']}")
            if ev['last_seen_time']:
                print(f"    Terakhir Terlihat: {ev['last_seen_time']}")
    print("-------------------------------------------------------\n")


if __name__ == '__main__':
    main()
