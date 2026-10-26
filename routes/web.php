<?php

use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;

Route::get('/',       [DemoController::class, 'home']);
Route::get('/menu',   [DemoController::class, 'menu']);
Route::get('/order',  [DemoController::class, 'orderForm']);
Route::post('/order', [DemoController::class, 'placeOrder']);