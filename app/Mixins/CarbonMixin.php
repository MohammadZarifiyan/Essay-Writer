<?php

namespace App\Mixins;

use Closure;
use IntlDateFormatter;
use MohammadZarifiyan\LaravelLocaleKit\LocaleKit;

class CarbonMixin
{
    public function timezoneAwareTraditionalIsoFormat(): Closure
    {
        return function (string $patternWithTimezone, string $patternFallback) {
            $timezone = LocaleKit::get('timezone', $this->locale);

            return is_null($timezone)
                ? $this->traditionalIsoFormat($patternFallback)
                : $this->copy()->timezone($timezone)->traditionalIsoFormat($patternWithTimezone);
        };
    }

    public function traditionalIsoFormat(): Closure
    {
        return function (string $pattern) {
            $localeIdentifier = LocaleKit::getIdentifier($this->locale);
            $calendarSystem = LocaleKit::get('calendar_system', $this->locale);
            $formatter = new IntlDateFormatter(
                sprintf('%s@calendar=%s', $localeIdentifier, $calendarSystem),
                timezone: $this->getTimezone(),
                calendar: IntlDateFormatter::TRADITIONAL,
                pattern: $pattern
            );

            return $formatter->format($this->toDateTime());
        };
    }
}
