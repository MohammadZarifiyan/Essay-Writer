<?php

return [
    \App\State\BotNotificationStatus\Pending::$name => 'در حال انتظار',
    \App\State\BotNotificationStatus\Processing::$name => 'در حال پردازش',
    \App\State\BotNotificationStatus\Finished::$name => 'تمام شده',
    \App\State\BotNotificationStatus\Canceled::$name => 'لغو شده',
    \App\State\BotNotificationStatus\Failed::$name => 'ناموفق',
];
