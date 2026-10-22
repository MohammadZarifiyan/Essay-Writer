<?php

namespace App\Telegram\Updates;

use App\Enums\EssayTone;
use Illuminate\Validation\Rule;
use MohammadZarifiyan\Telegram\FormUpdate;

class EssayToneUpdate extends FormUpdate
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
            'message.text' => trans('validation.attributes.essay_tone'),
        ];
    }

    protected function getAllowedButtons(): array
    {
        return array_map(
            fn (EssayTone $essayTone) => __('enums/essay-tone.' . $essayTone->value),
            EssayTone::cases()
        );
    }
}
