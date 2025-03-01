<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for your Telegram bot.
    | The bot token is fetched from the .env file.
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Telegram API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for Telegram Bot API requests.
    |
    */
    'api_url' => 'https://api.telegram.org/bot',
];