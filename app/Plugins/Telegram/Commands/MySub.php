<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;
use App\Utils\Helper;

class MySub extends Telegram {
    public $command = '/mysub';
    public $description = '获取订阅链接';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        if (!$message->is_private) return;
        $user = User::where('telegram_id', $message->chat_id)->first();
        if (!$user) {
            $telegramService->sendMessage($message->chat_id, '没有查询到您的用户信息，请先绑定账号', 'markdown');
            return;
        }

        $subscribeUrl = Helper::getSubscribeUrl($user->token);
        $text = "🔗 您的订阅链接\n———————————————\n{$subscribeUrl}\n———————————————\n请妥善保管，切勿泄露给他人。\n如需重置请发送 /resetsub";
        $telegramService->sendMessage($message->chat_id, $text);
    }
}
