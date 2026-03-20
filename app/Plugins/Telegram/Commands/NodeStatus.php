<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;
use App\Services\ServerService;

class NodeStatus extends Telegram {
    public $command = '/nodestatus';
    public $description = '查询节点状态';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        if (!$message->is_private) return;
        $user = User::where('telegram_id', $message->chat_id)->first();
        if (!$user) {
            $telegramService->sendMessage($message->chat_id, '没有查询到您的用户信息，请先绑定账号', 'markdown');
            return;
        }

        $servers = (new ServerService())->getAvailableServers($user);
        $total = count($servers);
        $online = 0;
        $offline = 0;
        $lines = [];

        foreach ($servers as $server) {
            if ($server['is_online']) {
                $online++;
            } else {
                $offline++;
            }
            $icon = $server['is_online'] ? '🟢' : '🔴';
            $type = $server['type'] === 'v2node' && isset($server['protocol'])
                ? strtoupper($server['protocol'])
                : strtoupper($server['type']);
            $lines[] = "{$icon} {$server['name']} [{$type}]";
        }

        $summary = "🟢 在线：{$online} | 🔴 离线：{$offline} | 总计：{$total}";
        $header = "📡 节点状态\n———————————————\n";
        $footer = "———————————————\n{$summary}";
        $text = $header;
        $displayed = 0;

        foreach ($lines as $line) {
            $remaining = $total - ($displayed + 1);
            $truncateNote = $remaining > 0 ? "...\n其余 {$remaining} 个节点已省略\n" : "";
            $potential = $text . $line . "\n" . $truncateNote . $footer;
            if (mb_strlen($potential) > 3500 && $displayed > 0) {
                $text .= "...\n其余 " . ($total - $displayed) . " 个节点已省略\n";
                break;
            }
            $text .= $line . "\n";
            $displayed++;
        }

        if ($total === 0) {
            $text .= "暂无可用节点\n";
        }

        $text .= $footer;
        $telegramService->sendMessage($message->chat_id, $text);
    }
}
