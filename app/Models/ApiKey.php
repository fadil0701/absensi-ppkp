<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = 'api_keys';

    protected $fillable = [
        'name',
        'api_key',
        'consid',
        'userkey',
        'secret_key',
        'description',
        'webhook_url',
        'allowed_ips',
        'scopes',
        'rate_limit',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'allowed_ips' => 'array',
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'secret_key',
    ];

    /**
     * Generate API Key, ConsID, UserKey, dan Secret Key
     */
    public static function generate(): array
    {
        $apiKey = 'ak_'.Str::random(32);
        $consid = 'cons_'.Str::random(32);
        $userkey = 'usr_'.Str::random(32);
        $secretKey = 'sk_'.Str::random(32);

        return [
            'api_key' => $apiKey,
            'consid' => $consid,
            'userkey' => $userkey,
            'secret_key' => $secretKey,
        ];
    }

    /**
     * Hash secret key sebelum disimpan
     */
    public function setSecretKeyAttribute($value): void
    {
        // Only hash if value is provided and not already hashed
        if (! empty($value)) {
            // Check if already hashed (bcrypt hash starts with $2y$, $2a$, or $2b$)
            if (! str_starts_with($value, '$2y$') &&
                ! str_starts_with($value, '$2a$') &&
                ! str_starts_with($value, '$2b$')) {
                $this->attributes['secret_key'] = Hash::make($value);
            } else {
                // Already hashed, store as is
                $this->attributes['secret_key'] = $value;
            }
        }
    }

    /**
     * Verify secret key
     */
    public function verifySecretKey(string $secretKey): bool
    {
        // Check if secret_key is already hashed (bcrypt)
        if (str_starts_with($this->secret_key, '$2y$') ||
            str_starts_with($this->secret_key, '$2a$') ||
            str_starts_with($this->secret_key, '$2b$')) {
            return Hash::check($secretKey, $this->secret_key);
        }

        // Direct comparison if not hashed (for backward compatibility)
        return hash_equals($this->secret_key, $secretKey);
    }

    /**
     * Check if API Key is valid
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if IP is allowed
     */
    public function isIpAllowed(?string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true; // No restriction
        }

        return in_array($ip, $this->allowed_ips);
    }

    /**
     * Check if scope is allowed
     */
    public function hasScope(string $scope): bool
    {
        if (empty($this->scopes)) {
            return true; // No restriction
        }

        return in_array($scope, $this->scopes) || in_array('*', $this->scopes);
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope untuk API Key aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
