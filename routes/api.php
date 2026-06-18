<?php

use App\Http\Controllers\Api\CekNikController;
use Illuminate\Support\Facades\Route;

Route::get('/cek-nik/{nik}', [CekNikController::class, 'cek']);
Route::post('/track-qr-download', [CekNikController::class, 'trackDownload']);
