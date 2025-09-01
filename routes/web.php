<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('admin');
});
Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
