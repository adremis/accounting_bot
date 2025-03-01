<?php

namespace App\Http\Controllers;

use App\Models\Partnership;
use App\Models\TelegramUser;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class TelegramBotController extends Controller
{
    protected $telegram;
    protected $commands = [
        '/start' => 'handleStart',
        '/invite' => 'handleInvite',
        '/accept' => 'handleAccept',
        '/debt' => 'handleDebt',
        '/demand' => 'handleDemand',
        '/balance' => 'handleBalance'
    ];

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handle(Request $request)
    {
        $update = $this->telegram->getWebhookUpdate();
        $message = $update->getMessage();
        $text = $message->getText();
        $chatId = $message->getChat()->getId();

        $command = strtok($text, ' ');
        $params = substr($text, strlen($command) + 1);

        if (isset($this->commands[$command])) {
            $method = $this->commands[$command];
            $this->$method($chatId, $params, $message);
        } else {
            $this->sendMessage($chatId, "Unknown command. Available commands:\n/invite - Create partnership invite\n/accept <token> - Accept partnership\n/debt <amount> <description> - Record debt\n/demand <amount> <description> - Record demand\n/balance - View balance");
        }
    }

    protected function handleStart($chatId, $params, $message)
    {
        $user = TelegramUser::firstOrCreate(
            ['telegram_id' => $chatId],
            [
                'username' => $message->getFrom()->getUsername(),
                'first_name' => $message->getFrom()->getFirstName(),
                'last_name' => $message->getFrom()->getLastName()
            ]
        );

        $this->sendMessage($chatId, "Welcome! Use /invite to create a new partnership or /accept <token> to join an existing one.");
    }

    protected function handleInvite($chatId, $params, $message)
    {
        $user = TelegramUser::where('telegram_id', $chatId)->first();
        $partnership = Partnership::create(['user1_id' => $user->id]);
        $token = $partnership->generateInviteToken();

        $this->sendMessage($chatId, "Share this token with your partner: $token\nThey can join using /accept $token");
    }

    protected function handleAccept($chatId, $token, $message)
    {
        $user = TelegramUser::where('telegram_id', $chatId)->first();
        $partnership = Partnership::where('invite_token', $token)->first();

        if (!$partnership) {
            $this->sendMessage($chatId, "Invalid or expired invite token.");
            return;
        }

        if ($partnership->user1_id === $user->id) {
            $this->sendMessage($chatId, "You cannot accept your own invite.");
            return;
        }

        $partnership->user2_id = $user->id;
        $partnership->accept();

        $this->sendMessage($chatId, "Partnership established! You can now record transactions.");
        $this->sendMessage($partnership->user1->telegram_id, "Your partnership invite has been accepted!");
    }

    protected function handleDebt($chatId, $params, $message)
    {
        $this->handleTransaction($chatId, $params, Transaction::TYPE_DEBT);
    }

    protected function handleDemand($chatId, $params, $message)
    {
        $this->handleTransaction($chatId, $params, Transaction::TYPE_DEMAND);
    }

    protected function handleTransaction($chatId, $params, $type)
    {
        $user = TelegramUser::where('telegram_id', $chatId)->first();
        $partnerships = $user->getAllPartnerships();

        if ($partnerships->isEmpty()) {
            $this->sendMessage($chatId, "You need to establish a partnership first. Use /invite to create one.");
            return;
        }

        $params = explode(' ', $params, 2);
        if (count($params) !== 2) {
            $this->sendMessage($chatId, "Invalid format. Use: /$type <amount> <description>");
            return;
        }

        [$amount, $description] = $params;
        if (!is_numeric($amount) || $amount <= 0) {
            $this->sendMessage($chatId, "Please provide a valid positive amount.");
            return;
        }

        $partnership = $partnerships->first();
        $transaction = Transaction::create([
            'partnership_id' => $partnership->id,
            'from_user_id' => $type === Transaction::TYPE_DEBT ? $user->id : $partnership->getOtherUserId($user->id),
            'to_user_id' => $type === Transaction::TYPE_DEBT ? $partnership->getOtherUserId($user->id) : $user->id,
            'amount' => $amount,
            'description' => $description,
            'type' => $type
        ]);

        $this->sendMessage($chatId, "$type recorded: $amount for $description");
        $this->sendMessage($partnership->getOtherUser($user)->telegram_id, 
            "New $type recorded by {$user->username}: $amount for $description");
    }

    protected function handleBalance($chatId, $params, $message)
    {
        $user = TelegramUser::where('telegram_id', $chatId)->first();
        $partnerships = $user->getAllPartnerships();

        if ($partnerships->isEmpty()) {
            $this->sendMessage($chatId, "You have no active partnerships.");
            return;
        }

        foreach ($partnerships as $partnership) {
            $balance = $this->calculateBalance($partnership, $user);
            $otherUser = $partnership->getOtherUser($user);
            $status = $balance > 0 ? "You are owed" : ($balance < 0 ? "You owe" : "No debt");
            $amount = abs($balance);

            $this->sendMessage($chatId, "Balance with {$otherUser->username}:\n$status: $amount");
        }
    }

    protected function calculateBalance(Partnership $partnership, TelegramUser $user)
    {
        $received = $partnership->transactions()
            ->where('to_user_id', $user->id)
            ->sum('amount');

        $sent = $partnership->transactions()
            ->where('from_user_id', $user->id)
            ->sum('amount');

        return $received - $sent;
    }

    protected function sendMessage($chatId, $text)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }
}