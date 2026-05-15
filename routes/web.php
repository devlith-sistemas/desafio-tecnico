<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


use App\Jobs\GenerateUsersExportJob;

Route::get('/teste-job', function () {
    GenerateUsersExportJob::dispatch();

    return 'Job enviado com sucesso';
});