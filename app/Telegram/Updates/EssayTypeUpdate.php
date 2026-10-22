<?php

namespace App\Telegram\Updates;

use App\Enums\EssayType;
use Illuminate\Validation\Rule;
use MohammadZarifiyan\Telegram\FormUpdate;

class EssayTypeUpdate extends FormUpdate
{
    public function rules(): array
    {
        return [
            'message.text' => [
                'bail',
                'required',
                'string',
                Rule::in($this->getAllowedButtons())
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'message.text' => trans('validation.attributes.essay_type'),
        ];
    }

    protected function getAllowedButtons(): array
    {
        return array_map(
            fn (EssayType $essayType) => __('enums/essay-type.' . $essayType->value),
            EssayType::cases()
        );
    }
}
