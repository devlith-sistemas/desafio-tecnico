<?php

namespace App\Models;

use App\Enums\StudentExportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentExport extends Model
{
    protected $fillable = [
        'requested_by_user_id',
        'status',
        'disk',
        'path',
        'file_name',
        'rows_processed',
        'started_at',
        'completed_at',
        'failed_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => StudentExportStatus::class,
            'rows_processed' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function markAsProcessing(): void
    {
        $this->forceFill([
            'status' => StudentExportStatus::Processing,
            'started_at' => now(),
            'failed_at' => null,
            'failure_reason' => null,
        ])->save();
    }

    public function markAsCompleted(int $rowsProcessed): void
    {
        $this->forceFill([
            'status' => StudentExportStatus::Completed,
            'rows_processed' => $rowsProcessed,
            'completed_at' => now(),
        ])->save();
    }

    public function markAsFailed(string $reason): void
    {
        $this->forceFill([
            'status' => StudentExportStatus::Failed,
            'failed_at' => now(),
            'failure_reason' => str($reason)->limit(1000)->toString(),
        ])->save();
    }

    public function isCompleted(): bool
    {
        return $this->status === StudentExportStatus::Completed;
    }
}
