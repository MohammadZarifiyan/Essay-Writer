<?php

namespace App\Models;

use App\Events\MandatoryMembershipChatUpdated;
use Illuminate\Database\Eloquent\Model;

class MandatoryMembershipChat extends Model
{
    protected $fillable = [
        'chat_id',
        'title',
        'url',
        'validity',
        'order_number',
    ];

    protected $casts = [
        'chat_id' => 'integer',
        'validity' => 'boolean',
        'order_number' => 'integer',
    ];

    protected $dispatchesEvents = [
        'updated' => MandatoryMembershipChatUpdated::class,
    ];
}
