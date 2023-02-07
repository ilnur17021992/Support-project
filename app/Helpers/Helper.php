<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Orchid\Platform\Models\Role;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

function test()
{
    return 'best';
}

function getTicketId(string $text)
{
    return Str::of($text)->match('/ID: ([0-9]+)/');
}

function checkPermission($permission)
{
    return auth()->user()?->hasAccess($permission);
}

function checkRole($role)
{
    return auth()->user()?->inRole(Role::firstWhere('slug', $role));
}

function checkExistsTicket($user)
{
    return $user->tickets()->whereIn('status', ['Processing', 'New'])->count() > 0;
}

function createUser($message)
{
    $bot = new BotApi(config('services.telegram_bot_api.token'));
    $telegramId = $message->getChat()->getId();
    $user = User::firstWhere('telegram_id', $telegramId);

    if (!$user && $telegramId > 0) {
        $fromUser = $message->getFrom();
        $userName = 'User' . $telegramId;
        $firstName = $fromUser->getFirstName();
        $email = 'user' . $telegramId . '@project.com';
        $password =  getPassword(10);
        $keyboard = new InlineKeyboardMarkup([[['text' => 'Войти в личный кабинет', 'url' => url('/')]]]);
        $role = Role::firstWhere('slug', 'user');

        $test = User::create([
            'telegram_id' => $telegramId,
            'name' => $userName,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        if (isset($role)) $test->addRole($role);

        $bot->sendMessage($telegramId, getWelcomeMessage($firstName, $email, $password), 'HTML', replyMarkup: $keyboard);
    }
}

function getWelcomeMessage($firstName, $email, $password)
{
    return
        'Приветствую вас, <b><u>' . $firstName . '</u></b>! Спасибо, что обратились в нашу службу поддержки! Ваши данные для входа: ' . "\n" .
        '<b>Почта</b>: <code>' . $email . "</code>\n" .
        '<b>Пароль</b>: <code>' . $password . "</code>\n";
}

function getPassword($length)
{
    // Для проверки пароля: ^(?!.*[lIO01])(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10}$
    // Массивы символов, кроме: lIO01
    $array_pass_0 = "23456789"; //0123456789
    $array_pass_1 = "abcdefghijkmnopqrstuvwxyz"; //abcdefghijklmnopqrstuvwxyz
    $array_pass_2 = "ABCDEFGHJKLMNPQRSTUVWXYZ"; //ABCDEFGHIJKLMNOPQRSTUVWXYZ

    $mode = [0, 1, 2];

    $password = "";
    for ($rnd = 0; $rnd < $length; $rnd++) {
        $array_pass = ${'array_pass_' . $mode[mt_rand(0, count($mode) - 1)]}; // выбираем случайный массив символов  
        $random_char = substr($array_pass, mt_rand(0, strlen($array_pass) - 1), 1);  // выбираем случайный символ из массива символов 		
        $password .= $random_char; // приписываем к строке пароля один символ  								
    }

    $blacklist = array(); // Числа которые нужно исключить

    //Проверка присутствует ли цифра, если нет то вставляем			
    for ($k = 0; $k < 1; $k++) {
        $num_pos = array_rand(array_diff(range(0, $length - 1), $blacklist), 1); //Генерируем позицию для цифры
        $blacklist[] = $num_pos; //Заносим позицию занятую номером в блеклист
        $password[$num_pos] = substr($array_pass_0, mt_rand(0, strlen($array_pass_0) - 1), 1);
    }

    //Проверка присутствует ли буква нижнего регистра, если нет то вставляем
    for ($k = 0; $k < 1; $k++) {
        $az_pos = array_rand(array_diff(range(0, $length - 1), $blacklist), 1); //Генерируем позицию для буквы
        $blacklist[] = $az_pos; //Заносим позицию занятую номером в блеклист
        $password[$az_pos] = substr($array_pass_1, mt_rand(0, strlen($array_pass_1) - 1), 1);
    }

    //Проверка присутствует ли буква ВЕРХНЕГО регистра, если нет то вставляем
    for ($k = 0; $k < 1; $k++) {
        $AZ_pos = array_rand(array_diff(range(0, $length - 1), $blacklist), 1); //Генерируем позицию для буквы
        $blacklist[] = $AZ_pos; //Заносим позицию занятую номером в блеклист
        $password[$AZ_pos] = substr($array_pass_2, mt_rand(0, strlen($array_pass_2) - 1), 1);
    }

    return $password;
}
