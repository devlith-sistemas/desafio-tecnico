<?php

namespace App\Actions\Students;

use App\Enums\StudentExportStatus;
use App\Jobs\ExportStudentsJob;
use App\Models\StudentExport;
use App\Models\User;
use Illuminate\Support\Str;

class RequestStudentsExport
{
    public function __invoke(User $user): StudentExport
    {
        $fileName = 'alunos-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6)).'.xlsx';
        $directory = trim((string) config('student-export.directory'), '/');

        $export = StudentExport::create([
            'requested_by_user_id' => $user->id,
            'status' => StudentExportStatus::Pending,
            'disk' => (string) config('student-export.disk', 'local'),
            'path' => $directory.'/'.$fileName,
            'file_name' => $fileName,
        ]);

        ExportStudentsJob::dispatch($export->id)
            ->onQueue((string) config('student-export.queue', 'default'))
            ->afterCommit();

        return $export;
    }
}
