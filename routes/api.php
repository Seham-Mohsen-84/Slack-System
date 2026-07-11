<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/messages', [ChatController::class, 'getMessages']);

Route::post('/messages', [ChatController::class, 'storeMessage']);