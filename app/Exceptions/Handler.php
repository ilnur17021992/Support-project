<?php

namespace App\Exceptions;

use Throwable;
use TelegramBot\Api\BotApi;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            $bot = new BotApi(config('services.telegram_bot_api.token'));

            $error =
                '<b>Time: </b>' . '<code>' . date('d.m.Y H:i:s') . '</code>' . "\n" .
                '<b>Type: </b>' . '<code>' . 'Error‼️' . '</code>' . "\n" .
                '<b>Env: </b>' . '<code>' . app()->environment() . '</code>' . "\n" .
                '<b>IP: </b>' . '<code>' . request()->ip() . '</code>' . "\n" .
                '<b>Method: </b>' . '<code>' . request()->method() . '</code>' . "\n" .
                '<b>Path: </b>' . '<code>' . request()->path() . '</code>' . "\n" .
                // '<b>Data: </b>' . '<code>' . request()->all() . '</code>' . "\n" .
                '<b>Description: </b>' . '<code>' . $e->getMessage() . '</code>' . "\n" .
                '<b>File: </b>' . '<code>' . $e->getFile() . '</code>' . "\n" .
                '<b>Line: </b>' . '<code>' . $e->getLine() . '</code>';

            $bot->sendMessage('-873224197', $error, 'HTML');
        });
    }
}
