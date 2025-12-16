<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satpelkes extends Model
{
    protected $table = 'satpelkes';
    
    protected $fillable = [
        'nama_satpelkes',
        'kode_satpelkes',
        'latitude',
        'longitude',
        'radius_absensi',
        'alamat',
        'is_aktif',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius_absensi' => 'integer',
        'is_aktif' => 'boolean',
    ];

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'satpelkes_id');
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'satpelkes_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }
}

