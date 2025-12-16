<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzinCuti extends Model
{
    protected $table = 'izin_cuti';
    
    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jenis',
        'keterangan',
        'status',
        'disetujui_oleh',
        'waktu_persetujuan',
        'alasan_penolakan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_persetujuan' => 'datetime',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'disetujui_oleh');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    public function scopeIzin($query)
    {
        return $query->where('jenis', 'izin');
    }

    public function scopeCuti($query)
    {
        return $query->where('jenis', 'cuti');
    }
}
