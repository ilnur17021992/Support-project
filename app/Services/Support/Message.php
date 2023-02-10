<?php

namespace App\Services\Support;

class Message
{
    public $user_id;
    public $telegram_message_id;
    public $message;
    public $file;

    public function __construct($user_id, $message, $file = null)
    {
        $this->user_id = $user_id;
        $this->message = $message;
        $this->file = $file;
    }

    public function __set($name, $value) {
        if ($name == 'telegram_message_id') {
            $this->telegram_message_id = $value;
        }
    }
}
