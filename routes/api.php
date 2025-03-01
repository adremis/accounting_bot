<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;

Route::post('/webhook/telegram', [TelegramBotController::class, 'handleWebhook']);