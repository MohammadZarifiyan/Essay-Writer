<?php

namespace App\Models;

use App\Casts\Serializable;
use Illuminate\Database\Eloquent\Model;

class TemporarilyData extends Model
{
    protected $fillable = [
        'key',
        'value',
        'cast',
    ];

    protected $casts = [
        'value' => Serializable::class,
        'cast' => Serializable::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
