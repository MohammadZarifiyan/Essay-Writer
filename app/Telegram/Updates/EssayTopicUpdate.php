<?php

namespace App\Telegram\Updates;

use MohammadZarifiyan\Telegram\FormUpdate;

class EssayTopicUpdate extends FormUpdate
{
    public function rules(): array
    {
        return [
            'message.text' => ['bail', 'required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'message.text' => trans('validation.attributes.essay_topic'),
        ];
    }
}
