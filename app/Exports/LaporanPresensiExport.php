<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPresensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Pegawai',
            'Unit Kerja',
            'Jenis Absensi',
            'Tanggal Absen',
            'Jam Masuk',
            'Jam Pulang',
            'Telat Masuk',
            'Pulang Cepat',
            'Keterangan'
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['nip'],
            $row['nama_pegawai'],
            $row['unit_kerja'],
            $row['jenis_absensi'],
            $row['tanggal_absen'],
            $row['jam_masuk'],
            $row['jam_pulang'],
            $row['telat_masuk'],
            $row['pulang_cepat'],
            $row['keterangan']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 15,  // NIP
            'C' => 25,  // Nama Pegawai
            'D' => 20,  // Unit Kerja
            'E' => 15,  // Jenis Absensi
            'F' => 15,  // Tanggal Absen
            'G' => 12,  // Jam Masuk
            'H' => 12,  // Jam Pulang
            'I' => 12,  // Telat Masuk
            'J' => 12,  // Pulang Cepat
            'K' => 30,  // Keterangan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C5AA0'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-size rows
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }
}
