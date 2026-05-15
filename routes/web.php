<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/teste-export', function () {
    return Excel::download(new UsersExport, 'usuarios.xlsx');
});