<?php

namespace App\Services\Support;

class Message
{
    public $user_id;
    public $telegram_message_id;
    public $text;
    public $file;

    public function __construct($user_id, $text, $file = null)
    {
        $this->user_id = $user_id;
        $this->text = $text;
        $this->file = $file;
    }

    public function __set($name, $value)
    {
        if ($name == 'telegram_message_id') {
            $this->telegram_message_id = $value;
        }
    }
}
