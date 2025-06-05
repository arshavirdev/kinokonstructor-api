<?php
namespace App\Service;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public static function formatException(\Throwable $e): string
    {
        $message = "*❗❗❗Exception Alert❗❗❗*\n";
        $message .= "`" . get_class($e) . "`\n";
        $message .= $e->getMessage() . "\n";
        $message .= "File: " . $e->getFile() . ":" . $e->getLine();

        return $message;
    }

    public static function sendMessage(string $message)
    {
        $token = config('services.telegram.bot_token');
        $chatIds = config('services.telegram.chat_ids');

        foreach ($chatIds as $chatId) {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        }
    }
}
