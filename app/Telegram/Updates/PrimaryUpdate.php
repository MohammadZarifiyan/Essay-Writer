<?php

namespace App\Telegram\Updates;

use Illuminate\Validation\Rule;
use MohammadZarifiyan\Telegram\FormUpdate;

class PrimaryUpdate extends FormUpdate
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
            'message.text' => trans('validation.attributes.selected_button'),
        ];
    }

    protected function getAllowedButtons(): array
    {
        $gainer = $this->gainer();
        $buttons = [
            trans('telegram/buttons.request-essay'),
        ];

        if ($gainer->hasRole('administrator')) {
            $buttons[] = trans('telegram/buttons.send-notification');
        }

        return $buttons;
    }
}
