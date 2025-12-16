<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satpelkes;

class SatpelkesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satpelkes = [
            [
                'nama_satpelkes' => 'Manajemen',
                'kode_satpelkes' => '001',
                'latitude' => -6,18191530,
                'longitude' => 106,82915070,
                'radius_absensi' => 50,
                'alamat' => 'Jl. Medan Merdeka Selatan No. 8-9 Blok E Lantai 2',
                'is_aktif' => true,
            ],
            [
                'nama_satpelkes' => 'Klinik Utama Balaikota',
                'kode_satpelkes' => '002',
                'latitude' => -6,18195980,
                'longitude' => 106,82914410,
                'radius_absensi' => 50,
                'alamat' => 'Jl. Medan Merdeka Selatan No. 8-9 Blok F Lantai 1',
                'is_aktif' => true,
            ],
            [
                'nama_satpelkes' => 'Klinik Pratama Satpelkes Balaikota',
                'kode_satpelkes' => '0003',
                'latitude' => -6,18191530,
                'longitude' => 106,82915070,
                'radius_absensi' => 50,
                'alamat' => 'Jl. Medan Merdeka Selatan No. 8-9 Blok E Lantai 1',
                'is_aktif' => true,
            ],
        ];

        foreach ($satpelkes as $data) {
            Satpelkes::create($data);
        }
    }
}
