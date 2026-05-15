<?php

namespace App\Jobs;

use App\Exports\UsersExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;

class GenerateUsersExportJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Excel::store(
            new UsersExport(),
            'exports/users.xlsx'
        );
    }
}