<?php

namespace App\Telegram\Updates;

use Illuminate\Validation\Rule;
use MohammadZarifiyan\Telegram\FormUpdate;

class YesOrNoAnswerUpdate extends FormUpdate
{
    public function rules(): array
    {
        return [
            'message.text' => [
                'bail',
                'required',
                'string',
                Rule::in([
                    trans('telegram/buttons.yes'),
                    trans('telegram/buttons.no'),
                ])
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'message.text' => trans('validation.attributes.selected_button'),
        ];
    }
}
