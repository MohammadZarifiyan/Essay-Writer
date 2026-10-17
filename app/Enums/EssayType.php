<?php

namespace App\Enums;

enum EssayType: string
{
    case Descriptive = 'descriptive';
    case Narrative = 'narrative';
    case Emotional = 'emotional';
    case Social = 'social';
    case Moral = 'moral';
    case Scientific = 'scientific';
    case Memoir = 'memoir';
    case Literary = 'literary';
}
