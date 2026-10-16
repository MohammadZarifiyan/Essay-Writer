<?php

namespace App\Services;

use App\Models\TemporarilyData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TemporarilyDataManager
{
    public function __construct(protected Model $user)
    {
        //
    }

    public function set(string $name, mixed $value, ?CastsAttributes $cast = null): static
    {
        $temporarilyData = TemporarilyData::whereBelongsTo($this->user)
            ->where('key', $name)
            ->firstOr(function () use ($name) {
                $temporarilyData = new TemporarilyData(['key' => $name]);
                $temporarilyData->user()->associate($this->user);

                return $temporarilyData;
            });

        $temporarilyData->fill([
            'value' => $cast ? $cast->set(new TemporarilyData, 'value', $value, []) : $value,
            'cast' => $cast
        ]);
        $temporarilyData->save();

        return $this;
    }

    public function findByName(string $name)
    {
        $temporarilyData = TemporarilyData::whereBelongsTo($this->user)->firstWhere('key', $name);

        if (is_null($temporarilyData)) {
            return null;
        }

        return is_null($temporarilyData->cast)
            ? $temporarilyData->value
            : $temporarilyData->cast->get(new TemporarilyData, 'value', $temporarilyData->value, []);
    }
}
