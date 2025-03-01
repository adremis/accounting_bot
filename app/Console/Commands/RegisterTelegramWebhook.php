<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

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
            return self::FAILURE;
        }

        if (!str_starts_with($webhookUrl, 'https://')) {
            $this->error('Webhook URL must start with https://');
            $this->info('Please update your TELEGRAM_WEBHOOK_URL in .env file to use HTTPS');
            $this->info('Example: https://your-domain.com/api/telegram/webhook');
            return self::FAILURE;
        }

        try {
            $response = Http::withOptions([
                'verify' => true,
                'timeout' => 30,
            ])->post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $webhookUrl
            ]);

            if ($response->successful()) {
                $this->info('Webhook registered successfully!');
                $this->info('Response: ' . $response->body());
                return self::SUCCESS;
            }

            $this->error('Failed to register webhook');
            $this->error('Response: ' . $response->body());
            return self::FAILURE;

        } catch (ConnectionException $e) {
            $this->error('Connection Error: ' . $e->getMessage());
            $this->info('\nTroubleshooting tips:');
            $this->info('1. Check your internet connection');
            $this->info('2. Verify if api.telegram.org is accessible from your network');
            $this->info('3. Check if your SSL certificates are up to date');
            $this->info('4. Try using a VPN if you\'re in a restricted region');
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('Unexpected Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}