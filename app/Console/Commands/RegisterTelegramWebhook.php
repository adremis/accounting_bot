<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RegisterTelegramWebhook extends Command
{
    protected $signature = 'telegram:register-webhook';
    protected $description = 'Register the webhook URL with Telegram API';

    public function handle()
    {
        $token = config('telegram.bot_token');
        $webhookUrl = config('telegram.webhook_url');

        if (empty($token) || empty($webhookUrl)) {
            $this->error('Telegram bot token or webhook URL is not configured in .env file');
            return 1;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $webhookUrl
        ]);

        if ($response->successful()) {
            $this->info('Webhook registered successfully!');
            $this->info('Response: ' . $response->body());
            return 0;
        }

        $this->error('Failed to register webhook');
        $this->error('Response: ' . $response->body());
        return 1;
    }
}