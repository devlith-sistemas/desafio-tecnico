<?php

use App\Http\Controllers\Students\DownloadStudentExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get(
    '/admin/student-exports/{studentExport}/download',
    DownloadStudentExportController::class,
)->name('student-exports.download');
