<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default password untuk semua user: password
        $defaultPassword = Hash::make('password');

        $pegawai = [
            // Admin
            [
                'nip' => 'ADM001',
                'nama' => 'Admin Sistem',
                'email' => 'admin@ppkp.go.id',
                'password' => $defaultPassword,
                'divisi' => 'Jabatan Pelaksana',
                'jabatan' => 'Administrator',
                'satpelkes_id' => 1,
                'role' => 'admin',
                'status' => 'aktif',
            ],
            // Pimpinan
            [
                'nip' => 'PMP001',
                'nama' => 'Kepala PPKP',
                'email' => 'pimpinan@ppkp.go.id',
                'password' => $defaultPassword,
                'divisi' => 'Struktural',
                'jabatan' => 'Kepala PPKP',
                'satpelkes_id' => 1,
                'role' => 'pimpinan',
                'status' => 'aktif',
            ],
            [
                'nip' => 'PMP002',
                'nama' => 'Kasubbag TU',
                'email' => 'pimpinan2@ppkp.go.id',
                'password' => $defaultPassword,
                'divisi' => 'Struktural',
                'jabatan' => 'Ka. Subbag TU',
                'satpelkes_id' => 2,
                'role' => 'pimpinan',
                'status' => 'aktif',
            ],
            // Pegawai
            [
                'nip' => '00419930107201710502',
                'nama' => 'Fadillah Asseggaf',
                'email' => 'fadilasgaf93@gmail.com',
                'password' => $defaultPassword,
                'divisi' => 'Jabatan Pelaksana',
                'jabatan' => 'Pengolah Data',
                'satpelkes_id' => 1,
                'role' => 'pegawai',
                'status' => 'aktif',
            ],
        ];

        foreach ($pegawai as $data) {
            Pegawai::create($data);
        }
    }
}
