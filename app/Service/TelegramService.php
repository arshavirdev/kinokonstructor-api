<?php
namespace App\Service;

use Route;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    public static function formatException(\Throwable $e): string
    {
        $appName = config('app.name');
        $message = "*❗❗❗Exception Alert: $appName ❗❗❗*\n";
        $message .= "`" . get_class($e) . "`\n";
        $message .= $e->getMessage() . "\n";
        $message .= "File: " . $e->getFile() . ":" . $e->getLine() . "\n";

        if (app()->runningInConsole() === false) {
            try {
                $request = request();
                $route = Route::current();

                $message .= "\n*Request Info:*\n";
                $message .= "Route: `" . $route->uri() . "`\n";
                $message .= "Method: `" . $route->getActionMethod() . "`\n";
                $message .= "Request Body: ```json\n" . json_encode($request->all(), JSON_PRETTY_PRINT) . "\n```\n";
            } catch (\Throwable $ex) {
                $message .= "\n⚠️ Failed to get request data: " . $ex->getMessage();
            }
        }

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
