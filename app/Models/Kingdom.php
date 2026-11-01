<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kingdom extends Model
{
    use HasFactory;

    protected $fillable = [
        'kd_number',
        'cooldown_hours',
        'last_notified_at',
        'replied_at',
        'replied_by',
        'ready_since',
        'reminder_count',
        'food',
        'wood',
        'stone',
        'gold',
        'locked_by',
        'locked_at'
    ];

    protected $casts = [
        'cooldown_hours' => 'float',
        'last_notified_at' => 'datetime',
        'replied_at' => 'datetime',
        'ready_since' => 'datetime',
        'reminder_count' => 'integer',
        'locked_at' => 'datetime',
    ];

    /**
     * Cek apakah KD sedang dikunci oleh operator (masa berlaku 30 menit)
     */
    public function isLocked(): bool
    {
        if (empty($this->locked_by) || !$this->locked_at) {
            return false;
        }

        // Kunci otomatis kedaluwarsa setelah 30 menit
        return $this->locked_at->greaterThan(now()->subMinutes(30));
    }

    /**
     * Ambil nama operator yang mengunci jika masih aktif
     */
    public function getActiveLockHolder(): ?string
    {
        return $this->isLocked() ? $this->locked_by : null;
    }

    /**
     * Sisa menit lock aktif
     */
    public function getLockMinutesLeft(): int
    {
        if (!$this->isLocked()) {
            return 0;
        }

        $expiresAt = $this->locked_at->copy()->addMinutes(30);
        return max(0, (int) round(now()->diffInMinutes($expiresAt, false)));
    }
}