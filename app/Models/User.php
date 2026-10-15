<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use MohammadZarifiyan\Telegram\Interfaces\HasStage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasStage
{
    use HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'bot_id',
        'telegram_id',
        'conversation_initiated',
        'stage',
    ];

    protected $casts = [
        'bot_id' => 'integer',
        'telegram_id' => 'integer',
        'conversation_initiated' => 'boolean',
    ];

    protected $hidden = ['remember_token'];

    public function getStage(): null|string|object
    {
        return $this->stage;
    }
}
