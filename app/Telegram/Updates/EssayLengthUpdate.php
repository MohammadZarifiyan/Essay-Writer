<?php

namespace App\Telegram\Updates;

use App\Enums\EssayLength;
use Illuminate\Validation\Rule;
use MohammadZarifiyan\Telegram\FormUpdate;

class EssayLengthUpdate extends FormUpdate
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
            'message.text' => trans('validation.attributes.essay_length'),
        ];
    }

    protected function getAllowedButtons(): array
    {
        return array_map(
            fn (EssayLength $essayLength) => __('enums/essay-length.' . $essayLength->value),
            EssayLength::cases()
        );
    }
}
