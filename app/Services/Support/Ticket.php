<?php

namespace App\Services\Support;

class Ticket
{
    public $user_id;
    public $title;
    public $department;
    public $status;

    public function __construct($user_id, $title, $department, $status)
    {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->department = $department;
        $this->status = $status;
    }

    public function __set($name, $value)
    {
        if ($name == 'telegram_message_id') {
            $this->telegram_message_id = $value;
        }
    }
}
