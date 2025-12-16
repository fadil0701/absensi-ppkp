<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pegawai';

    protected $fillable = [
        'nip',
        'nama',
        'email',
        'password',
        'divisi',
        'jabatan',
        'satpelkes_id',
        'device_id',
        'foto',
        'status',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'string',
            'role' => 'string',
        ];
    }

    public function satpelkes(): BelongsTo
    {
        return $this->belongsTo(Satpelkes::class);
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalPegawai::class);
    }

    public function presensiLog(): HasMany
    {
        return $this->hasMany(PresensiLog::class, 'pimpinan_id');
    }

    public function tugasLuar(): HasMany
    {
        return $this->hasMany(TugasLuar::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan' || $this->role === 'admin';
    }
}

