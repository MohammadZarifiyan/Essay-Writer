<?php

namespace App\Models;

use App\Enums\MessageCollection;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'collection_name',
        'data',
    ];

    protected $casts = [
        'collection_name' => MessageCollection::class,
        'data' => 'object',
    ];

    public function parent()
    {
        return $this->morphTo();
    }
}
