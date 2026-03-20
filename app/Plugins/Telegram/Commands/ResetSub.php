<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;
use App\Utils\Helper;

class ResetSub extends Telegram {
    public $command = '/resetsub';
    public $description = '重置订阅链接';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        if (!$message->is_private) return;
        $user = User::where('telegram_id', $message->chat_id)->first();
        if (!$user) {
            $telegramService->sendMessage($message->chat_id, '没有查询到您的用户信息，请先绑定账号', 'markdown');
            return;
        }

        $user->uuid = Helper::guid(true);
        $user->token = Helper::guid();
        if (!$user->save()) {
            abort(500, '重置失败');
        }

        $subscribeUrl = Helper::getSubscribeUrl($user->token);
        $text = "✅ 订阅链接已重置\n———————————————\n⚠️ 旧的订阅链接已永久失效\n\n新订阅链接：\n{$subscribeUrl}\n———————————————\n请在所有客户端中更新订阅链接";
        $telegramService->sendMessage($message->chat_id, $text);
    }
}
