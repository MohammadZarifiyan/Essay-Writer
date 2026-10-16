<?php

namespace App\Mixins;

use Closure;
use Illuminate\Support\Arr;

class ArrMixin
{
    public function chunkByFlatCount(): Closure
    {
        return function (array $array, int $chunkSize) {
            $result = [];
            $currentChunk = [];
            $currentFlatCount = 0;

            foreach ($array as $element) {
                $elementFlatCount = count(Arr::wrap($element));

                if ($currentFlatCount + $elementFlatCount > $chunkSize && count($currentChunk) > 0) {
                    $result[] = $currentChunk;
                    $currentChunk = [];
                    $currentFlatCount = 0;
                }

                $currentChunk[] = $element;
                $currentFlatCount += $elementFlatCount;
            }

            if (count($currentChunk) > 0) {
                $result[] = $currentChunk;
            }

            return $result;
        };
    }
}
