<?php

namespace App\Interfaces;

interface MessageCollectionHandlerInterface
{
    public function getRequestText(): string;

    public function saveAndFinish(array $messages): void;
}
