#!/bin/bash

# ==========================================================================
# CCTV NODE AUTOMATED INSTALLER (UNPAD ENTERPRISE)
# ==========================================================================
# Script ini mengotomatiskan instalasi server rekaman (Node) agar bisa
# langsung terhubung ke Master Server.
# ==========================================================================

set -e

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}====================================================${NC}"
echo -e "${BLUE}    CCTV RECORDING NODE INSTALLER (UNPAD)           ${NC}"
echo -e "${BLUE}====================================================${NC}"

# 1. CEK ROOT
if [ "$EUID" -ne 0 ]; then 
  echo -e "${RED}Silakan jalankan script ini sebagai root (Gunakan sudo).${NC}"
  exit
fi

# 2. INPUT USER
echo -e "${GREEN}>>> Konfigurasi Koneksi Master Server${NC}"
read -p "Masukkan IP Master Server (Contoh: 10.69.69.21): " MASTER_IP
read -p "Masukkan Database Master Name (Default: cctv_prod): " DB_NAME
DB_NAME=${DB_NAME:-cctv_prod}
read -p "Masukkan Database Master Username (Default: postgres): " DB_USER
DB_USER=${DB_USER:-postgres}
read -s -p "Masukkan Database Master Password: " DB_PASS
echo ""
read -p "Masukkan IP Node ini (IP Server ini): " NODE_IP
read -p "Berapa hari rekaman disimpan sebelum dihapus? (Default: 2): " RETENTION
RETENTION=${RETENTION:-2}

CURRENT_USER=$(logname)
HOME_DIR="/home/$CURRENT_USER"

# 3. INSTALASI DEPENDENSI
echo -e "${GREEN}>>> Menginstal dependensi sistem (Nginx, FFmpeg, Python)...${NC}"
apt update
apt install -y nginx ffmpeg python3-pip python3-venv wget curl git libpq-dev

# 4. DOWNLOAD GO2RTC
echo -e "${GREEN}>>> Mengunduh Go2RTC...${NC}"
ARCH=$(uname -m)
GO2RTC_VER="v1.9.4"
if [ "$ARCH" == "x86_64" ]; then
    URL="https://github.com/AlexxIT/go2rtc/releases/download/$GO2RTC_VER/go2rtc_linux_amd64"
elif [ "$ARCH" == "aarch64" ]; then
    URL="https://github.com/AlexxIT/go2rtc/releases/download/$GO2RTC_VER/go2rtc_linux_arm64"
else
    echo -e "${RED}Arsitektur $ARCH tidak didukung otomatis. Silakan download manual.${NC}"
    exit 1
fi

wget -O /usr/local/bin/go2rtc $URL
chmod +x /usr/local/bin/go2rtc

# 5. SETUP STRUKTUR FOLDER
echo -e "${GREEN}>>> Membuat struktur folder dan izin akses...${NC}"
mkdir -p "$HOME_DIR/storage/recordings"
mkdir -p "$HOME_DIR/cctv-scripts"
chown -R $CURRENT_USER:$CURRENT_USER "$HOME_DIR/storage"
chown -R $CURRENT_USER:$CURRENT_USER "$HOME_DIR/cctv-scripts"

# Izin agar Nginx bisa masuk ke folder Home
chmod o+x "$HOME_DIR"

# 6. SETUP PYTHON ENVIRONMENT
echo -e "${GREEN}>>> Mengatur Python Environment...${NC}"
pip3 install psycopg2-binary pyyaml python-dotenv --break-system-packages || pip3 install psycopg2-binary pyyaml python-dotenv

# Buat file .env
cat <<EOF > "$HOME_DIR/cctv-scripts/.env"
DB_HOST=$MASTER_IP
DB_PORT=5432
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASS
SERVER_RECORDER_IP=$NODE_IP
RETENTION_DAYS=$RETENTION
EOF

# 7. KONFIGURASI NGINX NODE
echo -e "${GREEN}>>> Mengkonfigurasi Nginx...${NC}"
cat <<EOF > /etc/nginx/sites-available/cctv-node
server {
    listen 80 default_server;
    server_name _;

    # Jalur File Rekaman MP4
    location /storage/ {
        root $HOME_DIR/;
        add_header Access-Control-Allow-Origin *;
        add_header Cache-Control no-cache;
        add_header Accept-Ranges bytes;
        max_ranges 100;
    }

    # Jalur Live Stream (Go2RTC)
    location / {
        proxy_pass http://127.0.0.1:1984;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    }
}
EOF

ln -sf /etc/nginx/sites-available/cctv-node /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
systemctl restart nginx

# 8. SETUP GO2RTC DEFAULT CONFIG
if [ ! -f "$HOME_DIR/go2rtc.yaml" ]; then
cat <<EOF > "$HOME_DIR/go2rtc.yaml"
api:
  listen: ":1984"
rtsp:
  listen: ":8554"
streams:
EOF
chown $CURRENT_USER:$CURRENT_USER "$HOME_DIR/go2rtc.yaml"
fi

# 9. SETUP SYSTEMD SERVICES
echo -e "${GREEN}>>> Mendaftarkan Service ke Systemd...${NC}"

# Service Go2RTC
cat <<EOF > /etc/systemd/system/go2rtc.service
[Unit]
Description=Go2RTC Streaming Server
After=network.target

[Service]
ExecStart=/usr/local/bin/go2rtc -config $HOME_DIR/go2rtc.yaml
WorkingDirectory=$HOME_DIR
User=$CURRENT_USER
Restart=always

[Install]
WantedBy=multi-user.target
EOF

# Service CCTV Sync (Python Agent)
cat <<EOF > /etc/systemd/system/cctv-sync.service
[Unit]
Description=CCTV Node Sync Agent
After=network.target go2rtc.service

[Service]
ExecStart=/usr/bin/python3 $HOME_DIR/cctv-scripts/sync_node.py
WorkingDirectory=$HOME_DIR/cctv-scripts
User=$CURRENT_USER
Restart=always

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable go2rtc cctv-sync
systemctl restart go2rtc

echo -e "${BLUE}====================================================${NC}"
echo -e "${GREEN}✅ INSTALASI BERHASIL!${NC}"
echo -e "${BLUE}====================================================${NC}"
echo -e "1. Pastikan Anda sudah menyalin file 'sync_node.py' ke: "
echo -e "   $HOME_DIR/cctv-scripts/sync_node.py"
echo -e "2. Jalankan service perekam dengan: "
echo -e "   sudo systemctl start cctv-sync"
echo -e "3. Pantau log dengan: "
echo -e "   sudo journalctl -u cctv-sync -f"
echo -e "${BLUE}====================================================${NC}"
