<?php

namespace App\Telegram\Updates;

use App\Enums\EducationalLevel;
use Illuminate\Validation\Rule;
use MohammadZarifiyan\Telegram\FormUpdate;

class EssayEducationalLevelUpdate extends FormUpdate
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
            'message.text' => trans('validation.attributes.educational_level'),
        ];
    }

    protected function getAllowedButtons(): array
    {
        return array_map(
            fn (EducationalLevel $educationalLevel) => __('enums/educational-level.' . $educationalLevel->value),
            EducationalLevel::cases()
        );
    }
}
