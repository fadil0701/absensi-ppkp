<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasLuar extends Model
{
    protected $table = 'tugas_luar';
    
    protected $fillable = [
        'pegawai_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi_tugas',
        'keterangan',
        'dokumen',
        'status',
        'disetujui_oleh',
        'waktu_persetujuan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
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

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }
}

