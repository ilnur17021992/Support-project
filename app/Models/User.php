<?php

namespace App\Models;

use Orchid\Platform\Models\User as Authenticatable;

class User extends Authenticatable
{
    private const ATTRIBUTES = [
        'id',
        'telegram_id',
        'name',
        'email',
        'password',
        'permissions',
        'created_at',
        'updated_at'
    ];

    protected $fillable = self::ATTRIBUTES;
    protected $allowedFilters = self::ATTRIBUTES;
    protected $allowedSorts = self::ATTRIBUTES;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getFullAttribute(): string
    {
        return $this->attributes['name'] . ' (' . $this->attributes['email'] . ')';
    }
}
