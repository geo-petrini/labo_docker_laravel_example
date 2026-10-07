<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('index'));

Route::get('/api/items', [ItemController::class, 'index']);
Route::post('/api/items', [ItemController::class, 'store']);
Route::get('/api/items/{id}', [ItemController::class, 'show']);
Route::put('/api/items/{id}', [ItemController::class, 'update']);
Route::delete('/api/items/{id}', [ItemController::class, 'destroy']);