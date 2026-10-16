<?php

namespace App\Models;

use App\Services\TemporarilyDataManager;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\Interfaces\HasStage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasStage, FilamentUser
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

    public function routeNotificationForTelegram(Notification $notification): int
    {
        return $this->telegram_id;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' ? $this->hasRole('administrator') : true;
    }

    public function temporarilyDataManager(): TemporarilyDataManager
    {
        return new TemporarilyDataManager($this);
    }

    public function temporarilyData()
    {
        return $this->hasMany(TemporarilyData::class);
    }
}
