<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presensi extends Model
{
    protected $table = 'presensi';
    
    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jenis',
        'waktu_absen',
        'latitude',
        'longitude',
        'accuracy',
        'device_id',
        'satpelkes_id',
        'jarak_ke_satpelkes',
        'status',
        'foto_asli',
        'foto_watermark',
        'ip_address',
        'user_agent',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_absen' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'jarak_ke_satpelkes' => 'decimal:2',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function satpelkes(): BelongsTo
    {
        return $this->belongsTo(Satpelkes::class);
    }

    public function presensiLog(): HasMany
    {
        return $this->hasMany(PresensiLog::class);
    }

    public function scopeCheckIn($query)
    {
        return $query->where('jenis', 'check_in');
    }

    public function scopeCheckOut($query)
    {
        return $query->where('jenis', 'check_out');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'OUT_ZONE_PENDING');
    }

    public function scopeUntukTanggal($query, $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }
}

