<?php

namespace App\Models;

use App\Events\BotNotificationCreated;
use App\State\BotNotificationStatus\BotNotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

class BotNotification extends Model
{
    use HasStates;

    protected $fillable = [
        'batch_id',
        'status',
        'quote',
        'total_notifications',
        'successful_notifications',
    ];

    protected $casts = [
        'status' => BotNotificationStatus::class,
        'quote' => 'boolean',
        'total_notifications' => 'integer',
        'successful_notifications' => 'integer',
    ];

    protected $dispatchesEvents = [
        'created' => BotNotificationCreated::class,
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
