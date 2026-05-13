<?php
try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=cctv_prod";
    $pdo = new PDO($dsn, "aay", "admcctvD@pnu1957", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $id = 140;
    $stmt = $pdo->prepare("SELECT id, nama_cctv, server_id, stream_url FROM cctvs WHERE id = ?");
    $stmt->execute([$id]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($c) {
        echo "✅ DATA KAMERA 140 DITEMUKAN\n";
        echo "Nama: " . $c['nama_cctv'] . "\n";
        echo "Server ID: " . ($c['server_id'] ?: 'NULL (BELUM DI-SET)') . "\n";
        echo "URL: " . ($c['stream_url'] ?: 'KOSONG') . "\n";
    } else {
        echo "❌ Kamera dengan ID $id TIDAK ADA di database.\n";
    }
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
