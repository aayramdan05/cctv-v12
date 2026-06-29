<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Master Data CCTV</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.3;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .header td {
            border: none;
            padding: 0;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
        }
        .meta-info {
            text-align: right;
            font-size: 9px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
        }
        .badge-online {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-offline {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-indoor {
            background-color: #ecfdf5;
            color: #065f46;
        }
        .badge-outdoor {
            background-color: #fff7ed;
            color: #9a3412;
        }
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 15px;
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .page-number:after {
            content: counter(page);
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">Laporan Master Data CCTV</div>
                    <div class="subtitle">Universitas Padjadjaran CCTV Monitoring System</div>
                </td>
                <td class="meta-info">
                    Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i') }}<br>
                    Dicetak oleh: {{ auth()->user()->name }} (Admin)<br>
                    Jumlah Kamera: {{ count($cctvs) }} unit
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">No.</th>
                <th style="width: 10%;">Kode CCTV</th>
                <th style="width: 16%;">Nama CCTV</th>
                <th style="width: 8%;">Merk</th>
                <th style="width: 10%;">IP Address</th>
                <th style="width: 8%;">Size/15m</th>
                <th style="width: 7%;">Penempatan</th>
                <th style="width: 13%;">Gedung & Kode</th>
                <th style="width: 11%;">Fakultas</th>
                <th style="width: 9%;">Server Node</th>
                <th style="width: 5%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cctvs as $index => $cctv)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-mono" style="font-weight: bold; color: #0891b2;">{{ $cctv->kode_cctv }}</td>
                    <td>
                        <div style="font-weight: bold; color: #1e293b;">{{ $cctv->nama_cctv }}</div>
                        <div style="font-size: 8px; color: #94a3b8; margin-top: 2px;">ID: #{{ $cctv->id }}</div>
                    </td>
                    <td>{{ $cctv->merk }}</td>
                    <td class="font-mono">{{ $cctv->ip ?? '-' }}</td>
                    <td style="font-weight: bold;">{{ $cctv->recordings_avg_size_mb ? round($cctv->recordings_avg_size_mb, 2) . ' MB' : '0 MB' }}</td>
                    <td>
                        @if($cctv->penempatan === 'Indoor')
                            <span class="badge badge-indoor">INDOOR</span>
                        @else
                            <span class="badge badge-outdoor">OUTDOOR</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: bold;">{{ $cctv->building ? $cctv->building->nama_gedung : '-' }}</div>
                        <div style="font-size: 8px; color: #64748b; margin-top: 1px;">Kode: {{ $cctv->building ? $cctv->building->kode_gedung : '-' }}</div>
                    </td>
                    <td>{{ $cctv->building ? $cctv->building->fakultas : '-' }}</td>
                    <td>
                        @if($cctv->server)
                            Node {{ $cctv->server_id }}<br>
                            <span style="font-size: 8px; color: #64748b;" class="font-mono">{{ $cctv->server->ip_address }}</span>
                        @else
                            <span style="color: #64748b;">Single/Master</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($cctv->status === 'online')
                            <span class="badge badge-online">ONLINE</span>
                        @else
                            <span class="badge badge-offline">OFFLINE</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data CCTV.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer Section -->
    <div class="footer">
        Halaman <span class="page-number"></span> &bull; CCTV Monitoring System Universitas Padjadjaran &bull; Dokumen Rahasia Internal
    </div>

</body>
</html>
