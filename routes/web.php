<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestGuzzleController;

Route::get('/', function () {
    return 'OK Laravel!';
});

Route::get('/test-guzzle', [TestGuzzleController::class, 'testGuzzle']);
