import sys
from onvif import ONVIFCamera

def test_onvif(ip, port, user, password):
    print("==================================================")
    print(f"🔍 Menguji Koneksi ONVIF ke {ip}:{port}...")
    print(f"👤 Username: {user}")
    print(f"🔑 Password: {'*' * len(password)}")
    print("==================================================")
    
    try:
        # 1. Tes Koneksi Dasar
        mycam = ONVIFCamera(ip, port, user, password)
        print("✅ 1. Koneksi Dasar: BERHASIL")
        
        # 2. Tes Get Device Information (Melihat Merek/Tipe)
        try:
            dev_info = mycam.devicemgmt.GetDeviceInformation()
            print(f"✅ 2. Informasi Perangkat: BERHASIL")
            print(f"   - Produsen (Brand)  : {getattr(dev_info, 'Manufacturer', 'Unknown')}")
            print(f"   - Model Kamera      : {getattr(dev_info, 'Model', 'Unknown')}")
            print(f"   - Versi Firmware    : {getattr(dev_info, 'FirmwareVersion', 'Unknown')}")
        except Exception as e_dev:
            print(f"❌ 2. Informasi Perangkat: GAGAL ({e_dev})")
        
        # 3. Tes Layanan Event (ONVIF Events)
        try:
            event_service = mycam.create_events_service()
            print("✅ 3. Event Service: TERSEDIA")
            
            # 4. Tes Pembuatan PullPoint (Untuk Motion Detection)
            try:
                pullpoint = mycam.create_pullpoint_service()
                print("✅ 4. PullPoint Service: DIDUKUNG")
                print("\n🎉 KESIMPULAN: Kamera ini mendukung penuh Motion Detection via ONVIF!")
            except Exception as e_pull:
                print(f"❌ 4. PullPoint Service: TIDAK DIDUKUNG ({e_pull})")
                print("\n⚠️ KESIMPULAN: Layanan event ada, tetapi PullPoint diblokir atau tidak didukung.")
        except Exception as e_event:
            print(f"❌ 3. Event Service: TIDAK TERSEDIA ({e_event})")
            print("\n❌ KESIMPULAN: Kamera tidak memiliki fitur event ONVIF (motion detection tidak bisa lewat ONVIF).")
            
    except Exception as e:
        print(f"❌ 1. Koneksi Dasar GAGAL: {e}")
        print("\n💡 SARAN DIAGNOSIS:")
        err_msg = str(e).lower()
        if "authorized" in err_msg or "auth" in err_msg:
            print("   -> ERROR OTORISASI: Username atau Password salah.")
            print("   -> CATATAN: Untuk kamera Hikvision/Dahua, Anda harus masuk ke Web UI kamera")
            print("      lalu aktifkan ONVIF secara manual dan tambahkan user baru khusus ONVIF.")
        elif "connection refused" in err_msg or "timeout" in err_msg:
            print("   -> TIMEOUT/REFUSED: Kamera tidak merespons pada port tersebut.")
            print("   -> Coba periksa apakah port ONVIF-nya benar (coba port: 80, 8080, 8899, atau 554).")
        else:
            print("   -> Periksa koneksi jaringan ke kamera atau pastikan protokol ONVIF aktif pada kamera.")
    print("==================================================")

if __name__ == "__main__":
    if len(sys.argv) < 5:
        print("Penggunaan:")
        print("  python3 diagnose_onvif.py <IP_CCTV> <PORT_ONVIF> <USER> <PASSWORD>")
        print("\nContoh:")
        print("  python3 diagnose_onvif.py 10.67.18.59 80 admin D1p4t1nangor#")
        sys.exit(1)
    
    ip_addr = sys.argv[1]
    port_num = int(sys.argv[2])
    username = sys.argv[3]
    pass_word = sys.argv[4]
    test_onvif(ip_addr, port_num, username, pass_word)
