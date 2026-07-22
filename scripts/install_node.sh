#!/bin/bash

# ==========================================================================
# CCTV NODE AUTOMATED INSTALLER (UNPAD ENTERPRISE)
# ==========================================================================
# Script ini mengotomatiskan instalasi server rekaman (Node) agar bisa
# langsung terhubung ke Master Server.
# ==========================================================================

set -e

# Dapatkan direktori script saat ini
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

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
read -p "Masukkan Database Master Username (Default: aay): " DB_USER
DB_USER=${DB_USER:-aay}
read -s -p "Masukkan Database Master Password: " DB_PASS
echo ""
read -p "Masukkan IP Node ini (IP Server ini): " NODE_IP
read -p "Berapa hari rekaman disimpan sebelum dihapus? (Default: 2): " RETENTION
RETENTION=${RETENTION:-2}

# Paksa user aay untuk kemudahan (sesuai instruksi)
TARGET_USER="aay"
HOME_DIR="/home/$TARGET_USER"

# Cek apakah user aay ada
if ! id "$TARGET_USER" &>/dev/null; then
    echo -e "${GREEN}>>> Membuat user $TARGET_USER...${NC}"
    useradd -m -s /bin/bash "$TARGET_USER"
    echo "$TARGET_USER:cctv123" | chpasswd
    usermod -aG sudo "$TARGET_USER"
fi

# 3. INSTALASI DEPENDENSI
echo -e "${GREEN}>>> Menginstal dependensi sistem (Nginx, FFmpeg, Python)...${NC}"
apt update || true
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
    echo -e "${RED}Arsitektur $ARCH tidak didukung otomatis.${NC}"
    exit 1
fi

wget -O /usr/local/bin/go2rtc $URL
chmod +x /usr/local/bin/go2rtc

# 5. SETUP STRUKTUR FOLDER
echo -e "${GREEN}>>> Membuat struktur folder dan izin akses...${NC}"
mkdir -p "/var/www/html/storage/recordings"
mkdir -p "$HOME_DIR/cctv-scripts"

# 6. COPY SCRIPTS (LOCAL ATAU CLONE VIA GITHUB)
if [ -f "$SCRIPT_DIR/cctv-sync.service" ] && [ -f "$SCRIPT_DIR/sync_node.py" ]; then
    echo -e "${GREEN}>>> Mendeteksi file installer lokal di $SCRIPT_DIR. Menggunakan file lokal...${NC}"
    cp -r "$SCRIPT_DIR"/* "$HOME_DIR/cctv-scripts/"
else
    echo -e "${GREEN}>>> Mengambil script dari GitHub (Sparse-Checkout)...${NC}"
    mkdir -p "$HOME_DIR/temp_clone"
    cd "$HOME_DIR/temp_clone"
    git init
    git remote add origin https://github.com/aayramdan05/cctv-v12.git
    git config core.sparseCheckout true
    echo "scripts/" >> .git/info/sparse-checkout
    echo "nginx/" >> .git/info/sparse-checkout
    git pull origin main

    # Pindahkan file ke lokasi akhir
    cp -r scripts/* "$HOME_DIR/cctv-scripts/"
    rm -rf "$HOME_DIR/temp_clone"
fi

chown -R $TARGET_USER:www-data "/var/www/html/storage"
chmod -R 775 "/var/www/html/storage"
chown -R $TARGET_USER:$TARGET_USER "$HOME_DIR/cctv-scripts"

# Izin agar Nginx bisa masuk ke folder Home
chmod o+x "$HOME_DIR"

# 7. SETUP PYTHON ENVIRONMENT
echo -e "${GREEN}>>> Mengatur Python Environment...${NC}"
pip3 install psycopg2-binary pyyaml python-dotenv onvif_zeep --break-system-packages || pip3 install psycopg2-binary pyyaml python-dotenv onvif_zeep

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
chown $TARGET_USER:$TARGET_USER "$HOME_DIR/cctv-scripts/.env"

# 8. KONFIGURASI NGINX NODE
echo -e "${GREEN}>>> Mengkonfigurasi Nginx...${NC}"
cat <<EOF > /etc/nginx/sites-available/cctv-node
server {
    listen 80 default_server;
    server_name _;

    allow $MASTER_IP;
    allow 127.0.0.1;
    deny all;

    # Jalur File Rekaman MP4
    location /storage/ {
        alias /var/www/html/storage/;
        add_header Access-Control-Allow-Origin *;
        add_header Cache-Control no-cache;
        add_header Accept-Ranges bytes;
    }

    # Jalur Live Stream (Go2RTC)
    location / {
        proxy_pass http://127.0.0.1:1984;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
    }
}
EOF

ln -sf /etc/nginx/sites-available/cctv-node /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
systemctl restart nginx

# 9. SETUP GO2RTC DEFAULT CONFIG
echo -e "${GREEN}>>> Mengatur konfigurasi Go2RTC...${NC}"
cat <<EOF > "$HOME_DIR/go2rtc.yaml"
api:
  listen: ":1984"
  origin: "*"
rtsp:
  listen: ":8554"
streams:
EOF
chown $TARGET_USER:$TARGET_USER "$HOME_DIR/go2rtc.yaml"

# 10. SETUP SYSTEMD SERVICES
echo -e "${GREEN}>>> Mendaftarkan Service ke Systemd...${NC}"

# Service Go2RTC
cat <<EOF > /etc/systemd/system/go2rtc.service
[Unit]
Description=Go2RTC Streaming Server
After=network.target

[Service]
ExecStart=/usr/local/bin/go2rtc -config $HOME_DIR/go2rtc.yaml
WorkingDirectory=$HOME_DIR
User=$TARGET_USER
Restart=always

[Install]
WantedBy=multi-user.target
EOF

# Service CCTV Sync
cp "$HOME_DIR/cctv-scripts/cctv-sync.service" /etc/systemd/system/
# Service CCTV ONVIF
cp "$HOME_DIR/cctv-scripts/cctv-onvif.service" /etc/systemd/system/

systemctl daemon-reload
systemctl enable go2rtc cctv-sync cctv-onvif
systemctl restart go2rtc cctv-sync cctv-onvif

echo -e "${BLUE}====================================================${NC}"
echo -e "${GREEN}✅ INSTALASI NODE BERHASIL!${NC}"
echo -e "${BLUE}====================================================${NC}"
echo -e "User: $TARGET_USER"
echo -e "Location: $HOME_DIR/cctv-scripts"
echo -e "Config: $HOME_DIR/go2rtc.yaml"
echo -e "Services: go2rtc, cctv-sync, cctv-onvif"
echo -e "Monitor: sudo journalctl -u cctv-sync -f"
echo -e "${BLUE}====================================================${NC}"
