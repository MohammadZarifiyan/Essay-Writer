<?php

namespace App\Models;

use App\Enums\EducationalLevel;
use App\Enums\EssayTone;
use App\Enums\EssayType;
use App\State\EssayStatus\EssayStatus;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

class Essay extends Model
{
    use HasStates;

    protected $fillable = [
        'status',
        'topic',
        'educational_level',
        'type',
        'tone',
        'requested_word_count',
    ];

    protected $casts = [
        'status' => EssayStatus::class,
        'educational_level' => EducationalLevel::class,
        'type' => EssayType::class,
        'tone' => EssayTone::class,
        'requested_word_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
