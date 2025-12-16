<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiLog extends Model
{
    protected $table = 'presensi_log';
    
    protected $fillable = [
        'presensi_id',
        'pimpinan_id',
        'action',
        'catatan',
        'waktu_action',
    ];

    protected $casts = [
        'waktu_action' => 'datetime',
    ];

    public function presensi(): BelongsTo
    {
        return $this->belongsTo(Presensi::class);
    }

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pimpinan_id');
    }
}


