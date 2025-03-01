<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    protected $apiUrl;
    protected $token;

    public function __construct()
    {
        $this->token = config('telegram.bot_token');
        $this->apiUrl = config('telegram.api_url') . $this->token;
    }

    public function handleWebhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram webhook received:', $update);

            if (isset($update['message'])) {
                $chatId = $update['message']['chat']['id'];
                $messageText = $update['message']['text'] ?? '';

                // Example: Echo back the received message
                $this->sendMessage($chatId, "Received: {$messageText}");
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function sendMessage($chatId, $text)
    {
        return Http::post($this->apiUrl . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }
}