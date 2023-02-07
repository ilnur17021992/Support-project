<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, Filterable, AsSource;

    private const ATTRIBUTES = [
        'id',
        'user_id',
        'title',
        'department',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $fillable = self::ATTRIBUTES;
    protected $allowedFilters = self::ATTRIBUTES;
    protected $allowedSorts = self::ATTRIBUTES;

    public const STATUS = [
        'New' => 'Новый',
        'Processing' => 'В процессе',
        'Closed' => 'Закрыт',
    ];

    public const DEPARTMENT = [
        'Other' => 'Общие вопросы',
        'Moderation' => 'Модерация',
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
