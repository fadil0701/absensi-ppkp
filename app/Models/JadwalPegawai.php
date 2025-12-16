<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPegawai extends Model
{
    protected $table = 'jadwal_pegawai';
    
    protected $fillable = [
        'pegawai_id',
        'hari',
        'jam_masuk',
        'jam_keluar',
        'toleransi_telat',
        'is_aktif',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'toleransi_telat' => 'integer',
        'is_aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Accessor untuk jam_masuk (format: H:i)
     */
    public function getJamMasukAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // Jika sudah string format H:i, return langsung
        if (is_string($value) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
            return substr($value, 0, 5); // Ambil hanya H:i
        }
        
        // Jika Carbon instance, format ke H:i
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('H:i');
        }
        
        return $value;
    }

    /**
     * Accessor untuk jam_keluar (format: H:i)
     */
    public function getJamKeluarAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // Jika sudah string format H:i, return langsung
        if (is_string($value) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
            return substr($value, 0, 5); // Ambil hanya H:i
        }
        
        // Jika Carbon instance, format ke H:i
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('H:i');
        }
        
        return $value;
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeUntukTanggal($query, $tanggal)
    {
        return $query->where(function ($q) use ($tanggal) {
            $q->whereNull('tanggal_mulai')
              ->orWhere('tanggal_mulai', '<=', $tanggal);
        })
        ->where(function ($q) use ($tanggal) {
            $q->whereNull('tanggal_selesai')
              ->orWhere('tanggal_selesai', '>=', $tanggal);
        });
    }

    /**
     * Get jadwal untuk hari tertentu
     */
    public function scopeUntukHari($query, $tanggal)
    {
        $hari = \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd');
        
        // Map hari Indonesia ke Inggris
        $hariMap = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday',
        ];
        
        $hariInggris = $hariMap[$hari] ?? null;
        $hariIndo = $hari;
        
        return $query->where(function ($q) use ($hariInggris, $hariIndo) {
            $q->whereNull('hari')
              ->orWhere('hari', $hariIndo)
              ->orWhere('hari', $hariInggris);
        });
    }
}

