<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Models\StudentExport;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadStudentExportController extends Controller
{
    public function __invoke(StudentExport $studentExport): StreamedResponse
    {
        abort_unless($studentExport->requested_by_user_id === auth()->id(), 403);
        abort_unless($studentExport->isCompleted(), 409, 'A exportação ainda não foi concluída.');

        $disk = Storage::disk($studentExport->disk);

        abort_unless($studentExport->path && $disk->exists($studentExport->path), 404);

        $stream = $disk->readStream($studentExport->path);

        abort_if($stream === false, 404);

        return response()->streamDownload(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, $studentExport->file_name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
