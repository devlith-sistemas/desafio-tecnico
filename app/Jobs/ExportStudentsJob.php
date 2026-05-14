<?php

namespace App\Jobs;

use App\Exports\Students\StudentExportNotifier;
use App\Exports\Students\StudentsSpreadsheetExporter;
use App\Models\StudentExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class ExportStudentsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 0;

    public int $tries = 1;

    public function __construct(
        public readonly int $studentExportId,
    ) {}

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('student-export:'.$this->studentExportId))->expireAfter(21600),
        ];
    }

    public function handle(StudentsSpreadsheetExporter $exporter, StudentExportNotifier $notifier): void
    {
        $export = StudentExport::with('requestedBy')->findOrFail($this->studentExportId);

        if ($export->isCompleted()) {
            return;
        }

        $export->markAsProcessing();

        $rowsProcessed = $exporter->write($export);

        $export->refresh();
        $export->load('requestedBy');
        $export->markAsCompleted($rowsProcessed);
        $export->refresh();
        $export->load('requestedBy');

        $notifier->completed($export);
    }

    public function failed(?Throwable $exception): void
    {
        $export = StudentExport::with('requestedBy')->find($this->studentExportId);

        if (! $export || $export->isCompleted()) {
            return;
        }

        $export->markAsFailed($exception?->getMessage() ?? 'Erro desconhecido ao exportar alunos.');
        $export->refresh();
        $export->load('requestedBy');

        app(StudentExportNotifier::class)->failed($export);
    }
}
