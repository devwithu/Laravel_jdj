<?php

use App\Http\Controllers\UpbitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/profit', [UpbitController::class, 'profit']);
