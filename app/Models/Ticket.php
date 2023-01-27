<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, Filterable, AsSource;

    protected $fillable = [
        'user_id', 'title', 'department', 'status'
    ];

    protected $allowedFilters = [
        'id', 'user_id', 'title', 'department', 'status', 'created_at', 'updated_at'
    ];

    protected $allowedSorts = [
        'id', 'user_id', 'title', 'department', 'status', 'created_at', 'updated_at'
    ];

    public const STATUS = [
        'New' => 'Новый',
        'Processing' => 'В процессе',
        'Closed' => 'Закрыт',
    ];

    public const DEPARTMENT = [
        'Moderation' => 'Модерация',
        'Other' => 'Общие вопросы',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
