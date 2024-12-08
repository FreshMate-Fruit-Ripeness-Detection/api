<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FruitDetectionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/detect-ripeness', [FruitDetectionController::class, 'detectRipeness']);
    Route::get('/diseases-fruit', [FruitDetectionController::class, 'getHistory']);
    Route::get('/supported-fruits', [FruitDetectionController::class, 'getSupportedFruits']);
});