<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CsvImport extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'stored_filename',
        'file_path',
        'file_size',
        'status',
        'import_type',
        'total_rows',
        'success_rows',
        'failed_rows',
        'duplicate_rows',     //   TAMBAHKAN
        'error_log',
        'processed_at',
        'started_at',         //   TAMBAHKAN
        'completed_at',       //   TAMBAHKAN
    ];

    protected $casts = [
        'file_size' => 'integer',
        'total_rows' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer',
        'duplicate_rows' => 'integer',  //   TAMBAHKAN
        'processed_at' => 'datetime',
        'started_at' => 'datetime',      //   TAMBAHKAN
        'completed_at' => 'datetime',    //   TAMBAHKAN
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== ACCESSORS ====================

    /**
     * Get formatted file size
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return '—';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return number_format($bytes, $i > 1 ? 2 : 0) . ' ' . $units[$i];
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'failed' => 'Gagal',
            default => '—'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'badge-pending',
            'processing' => 'badge-processing',
            'completed' => 'badge-completed',
            'failed' => 'badge-failed',
            default => ''
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bi-clock',
            'processing' => 'bi-hourglass-split',
            'completed' => 'bi-check-circle',
            'failed' => 'bi-x-circle',
            default => 'bi-circle'
        };
    }

    /**
     * Get success rate
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows <= 0) {
            return 0;
        }
        return round(($this->success_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->started_at || !$this->completed_at) {
            return '—';
        }

        $seconds = $this->started_at->diffInSeconds($this->completed_at);

        if ($seconds < 60) {
            return $seconds . ' detik';
        }

        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        if ($minutes < 60) {
            return $minutes . 'm ' . $seconds . 's';
        }

        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;

        return $hours . 'j ' . $minutes . 'm ' . $seconds . 's';
    }

    /**
     * Check if import has errors
     */
    public function getHasErrorsAttribute(): bool
    {
        return $this->failed_rows > 0 || !empty($this->error_log);
    }

    /**
     * Check if import has duplicates
     */
    public function getHasDuplicatesAttribute(): bool
    {
        return ($this->duplicate_rows ?? 0) > 0;
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ==================== SCOPES ====================

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== METHODS ====================

    public function markAsProcessing(): self
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
        return $this;
    }

    public function markAsCompleted(): self
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'processed_at' => now(),
        ]);
        return $this;
    }

    public function markAsFailed(string $error = null): self
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'processed_at' => now(),
            'error_log' => $error ?: $this->error_log,
        ]);
        return $this;
    }

    public function updateStatistics(int $totalRows, int $successRows, int $failedRows, int $duplicateRows = 0): self
    {
        $this->update([
            'total_rows' => $totalRows,
            'success_rows' => $successRows,
            'failed_rows' => $failedRows,
            'duplicate_rows' => $duplicateRows,
        ]);
        return $this;
    }

    public function deleteFile(): bool
    {
        if (!$this->file_path) {
            return true;
        }
        return Storage::disk('public')->delete($this->file_path);
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = 'pending';
            }
            if (empty($model->import_type)) {
                $model->import_type = 'transactions';
            }
            if (!isset($model->duplicate_rows)) {
                $model->duplicate_rows = 0;
            }
        });

        static::deleting(function ($model) {
            $model->deleteFile();
        });
    }
}
