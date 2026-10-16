<?php

return [
    'plural-label' => 'اعلان‌های ربات',
    'model-label' => 'اعلان ربات',
    'table' => [
        'empty-state-heading' => 'هیچ اعلان رباتی یافت نشد.',
        'columns' => [
            'id' => 'شناسه',
            'status' => 'وضعیت',
            'quote' => 'نقل قول',
            'total-notifications' => 'تعداد کل اعلان‌ها',
            'successful-notifications' => 'اعلان‌های موفق',
        ],
        'filters' => [
            'status' => 'وضعیت',
            'quote' => 'نقل قول',
        ],
        'record-actions' => [
            'cancel' => [
                'label' => 'لغو',
                'modal-heading' => 'لغو اعلان',
                'modal-description' => 'آیا از لغو این اعلان اطمینان دارید؟',
            ],
        ],
    ],
    'notifications' => [
        'failed-to-cancel-the-notification' => 'لغو اعلان با خطا مواجه شد.',
        'notification-canceled' => 'اعلان لغو شد.',
    ],
];
