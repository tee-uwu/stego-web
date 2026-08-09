<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StegoController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/encode', [StegoController::class, 'encode'])->name('stego.encode');
Route::post('/decode', [StegoController::class, 'decode'])->name('stego.decode');