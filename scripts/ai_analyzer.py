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

# 1. Matikan verbose C++ log PyTorch & NNPACK warnings di lingkungan CPU
os.environ['TORCH_CPP_LOG_LEVEL'] = 'ERROR'
warnings.filterwarnings('ignore')

try:
    import torch
    # Matikan NNPACK flag agar tidak memicu error "Could not initialize NNPACK" di CPU
    try:
        if hasattr(torch.backends, 'nnpack'):
            torch.backends.nnpack.set_flags(False)
    except Exception:
        pass
    # Alokasikan thread CPU optimal (maksimal 4 thread agar server tetap adil membagi CPU)
    torch.set_num_threads(max(1, min(4, os.cpu_count() or 2)))
except ImportError:
    pass

try:
    import cv2
    import numpy as np
except ImportError:
    print("[ERROR] OpenCV (cv2) or numpy not installed. Run: pip install opencv-python numpy")
    sys.exit(1)

try:
    from ultralytics import YOLO
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

    def process_video(self, video_source, sample_fps=1, max_duration_sec=None, conf_threshold=0.25):
        """
        Menganalisis file rekaman video / stream CCTV
        """
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

        print(f"[AI] Memulai analisa: {video_source}")
        print(f"[AI] Total Frame: {total_frames} (Durasi: {int(video_duration_sec // 60)}m {int(video_duration_sec % 60)}s), FPS Asli: {orig_fps:.1f}")
        print(f"[AI] Sampling Rate: {sample_fps} FPS (interval 1 frame tiap {frame_interval} frame, target proses: {total_sampled} frame di CPU, Ambang Batas Conf: {conf_threshold})")

        frame_idx = 0
        sampled_count = 0
        current_time = datetime.now() - timedelta(seconds=int(video_duration_sec))

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

            # Tampilkan progress bar setiap 5% atau 50 frame agar user tahu proses CPU berjalan lancar
            if sampled_count == 1 or sampled_count % max(1, int(total_sampled / 20)) == 0 or sampled_count == total_sampled:
                pct = int((sampled_count / total_sampled) * 100)
                pos_m = int(current_sec // 60)
                pos_s = int(current_sec % 60)
                tot_m = int(video_duration_sec // 60)
                tot_s = int(video_duration_sec % 60)
                print(f"⏳ [AI Progress: {pct}%] ({sampled_count}/{total_sampled} frame) | Menit ke {pos_m:02d}:{pos_s:02d} dari {tot_m:02d}:{tot_s:02d}", flush=True)

            # Run YOLO Tracking secara eksplisit di CPU
            results = self.model.track(
                source=frame,
                persist=True,
                classes=list(TARGET_CLASSES.keys()),
                verbose=False,
                conf=conf_threshold,
                device='cpu'
            )

            current_frame_tracks = set()
            current_persons_boxes = []

            # Jika mode demo, simulasikan juga objek laptop di meja yang aktif di frame 0-40 lalu hilang
            if 'demo' in str(video_source).lower() and frame_idx < 40:
                demo_laptop_id = 999
                current_frame_tracks.add(demo_laptop_id)
                laptop_bbox = np.array([280, 320, 380, 390])
                if demo_laptop_id not in self.tracked_objects:
                    self.tracked_objects[demo_laptop_id] = {
                        'track_id': demo_laptop_id,
                        'class_name': 'laptop',
                        'color': 'silver',
                        'confidence': 0.94,
                        'first_seen': frame_time,
                        'last_seen': frame_time,
                        'bbox': laptop_bbox,
                        'frames_stationary': 10,
                        'snapshot_before': frame.copy(),
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
                                'snapshot_before': frame.copy(),
                                'snapshot_during': None,
                                'snapshot_after': None,
                                'status': 'active'
                            }

                            # Catat event kendaraan atau orang masuk
                            if cls_name in ['car', 'motorcycle', 'truck', 'bus']:
                                self._trigger_event(
                                    event_type='vehicle_flow',
                                    object_type=cls_name,
                                    color=color,
                                    confidence=conf,
                                    event_time=frame_time,
                                    snapshot=frame,
                                    narrative_hint=f"{OBJECT_NAMES_ID.get(cls_name, cls_name)} warna {color} terdeteksi melintas pada jam {time_str}."
                                )
                            elif cls_name == 'person':
                                self._trigger_event(
                                    event_type='presence_detected',
                                    object_type='person',
                                    color=color,
                                    confidence=conf,
                                    event_time=frame_time,
                                    snapshot=frame,
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
                                # Update snapshot awal saat masih tenang
                                if obj['frames_stationary'] == 3:
                                    obj['snapshot_before'] = frame.copy()
                            else:
                                obj['frames_stationary'] = 0

                            obj['bbox'] = xyxy

            # Periksa objek yang HILANG / TIDAK LAGI TERDETEKSI
            # Terutama barang berharga: laptop, backpack, cell phone, handbag
            for track_id, obj in list(self.tracked_objects.items()):
                if obj['status'] == 'active' and track_id not in current_frame_tracks:
                    # Objek tidak terdeteksi di frame ini
                    if track_id not in self.disappeared_candidates:
                        self.disappeared_candidates[track_id] = {
                            'missing_count': 1,
                            'disappeared_at': frame_time
                        }
                    else:
                        self.disappeared_candidates[track_id]['missing_count'] += 1

                    # Jika hilang selama beberapa sample frame berturut-turut
                    if self.disappeared_candidates[track_id]['missing_count'] >= 3:
                        obj_cls = obj['class_name']
                        # Cek apakah ini barang yang berpotensi hilang/dipindahkan
                        if obj_cls in ['laptop', 'backpack', 'cell phone', 'handbag']:
                            obj['status'] = 'disappeared'
                            obj['snapshot_after'] = frame.copy()

                            # Cek korelasi apakah ada orang (person) di dekatnya saat terakhir terlihat
                            suspect_nearby = len(current_persons_boxes) > 0

                            narrative_hint = (
                                f"PERINGATAN: {OBJECT_NAMES_ID.get(obj_cls, obj_cls)} warna {obj['color']} "
                                f"yang semula berada di lokasi tidak lagi terlihat sejak {time_str} WIB. "
                                f"Terakhir terpantau pada {obj['last_seen'].strftime('%H:%M:%S')} WIB."
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
                                snapshot=frame,
                                snapshot_before=obj.get('snapshot_before'),
                                snapshot_after=obj.get('snapshot_after'),
                                narrative_hint=narrative_hint,
                                metadata={'suspect_detected': suspect_nearby, 'tracker_id': track_id}
                            )

                            del self.disappeared_candidates[track_id]
                elif track_id in current_frame_tracks and track_id in self.disappeared_candidates:
                    # Objek muncul kembali (hanya tertutup sementara / occlusion)
                    del self.disappeared_candidates[track_id]

            frame_idx += 1

        cap.release()
        print(f"[AI] Analisa selesai! Total {len(self.events_log)} event dicatat.")
        return self.events_log

    def _trigger_event(self, event_type, object_type, color, confidence, event_time, 
                       snapshot=None, snapshot_before=None, snapshot_after=None,
                       last_seen_time=None, narrative_hint="", metadata=None):
        """Membuat event terstruktur, meminta narasi Ollama, dan menyimpan ke log"""

        # Generate narasi via Ollama (dengan fallback otomatis)
        narrative = query_ollama_narrative(
            prompt=narrative_hint,
            ollama_url=self.ollama_url,
            fallback=narrative_hint
        )

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

        # Print highlight ke konsol
        icon = "🚨 [BARANG HILANG]" if event_type == 'missing_item' else "🚗 [KENDARAAN]"
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
    parser.add_argument("--fps", type=int, default=1, help="Sampling FPS (default: 1 FPS, optimal di CPU)")
    parser.add_argument("--conf", type=float, default=0.25, help="Ambang batas deteksi confidence YOLO (default: 0.25)")
    parser.add_argument("--limit-sec", type=int, default=None, help="Batas durasi detik yang ingin diuji coba (misal: 60 untuk 1 menit pertama)")
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

    events = analyzer.process_video(video_path, sample_fps=args.fps, max_duration_sec=args.limit_sec, conf_threshold=args.conf)

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
