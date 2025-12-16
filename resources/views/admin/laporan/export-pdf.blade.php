<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
        }
        .header p {
            margin: 3px 0;
            font-size: 8pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8pt;
        }
        table th,
        table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        table th {
            background-color: #2c5aa0;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 15px;
            font-size: 7pt;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PRESENSI</h2>
        <p>Sistem Absensi PPKP</p>
        <p>Periode: {{ $tanggalMulai }} s/d {{ $tanggalSelesai }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">NIP</th>
                <th style="width: 18%;">Nama Pegawai</th>
                <th style="width: 15%;">Unit Kerja</th>
                <th style="width: 12%;">Jenis Absensi</th>
                <th style="width: 12%;">Tanggal Absen</th>
                <th style="width: 10%;">Jam Masuk</th>
                <th style="width: 10%;">Jam Pulang</th>
                <th style="width: 10%;">Telat Masuk</th>
                <th style="width: 10%;">Pulang Cepat</th>
                <th style="width: 16%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td class="text-center">{{ $row['no'] }}</td>
                    <td>{{ $row['nip'] }}</td>
                    <td>{{ $row['nama_pegawai'] }}</td>
                    <td>{{ $row['unit_kerja'] }}</td>
                    <td class="text-center">{{ $row['jenis_absensi'] }}</td>
                    <td class="text-center">{{ $row['tanggal_absen'] }}</td>
                    <td class="text-center">{{ $row['jam_masuk'] }}</td>
                    <td class="text-center">{{ $row['jam_pulang'] }}</td>
                    <td class="text-center">{{ $row['telat_masuk'] }}</td>
                    <td class="text-center">{{ $row['pulang_cepat'] }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i:s') }}</p>
    </div>
</body>
</html>


