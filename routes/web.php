<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/download-export', function () {
    $filename = cache('last_users_export');

    abort_if(!$filename, 404);

    return response()->download(
        storage_path('app/public/exports/' . $filename)
    );
});