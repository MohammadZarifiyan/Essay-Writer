<?php

namespace App\Telegram\UpdateHandlers\PrimaryStage;

use App\Interfaces\HandlingUpdateStrategyInterface;
use MohammadZarifiyan\Telegram\Update;

class RequestEssay implements HandlingUpdateStrategyInterface
{
    public function __construct(protected Update $update)
    {
        //
    }

    public function handle(): void
    {
        // TODO: Implement handle() method.
    }
}
