<?php

use Carbon\Carbon;

if (! function_exists('localized_digits')) {
  function localized_digits(string $value): string
    {
        if (app()->getLocale() !== 'ar') {
            return $value;
        }

        return strtr($value, [
            '0' => '٠',
            '1' => '١',
            '2' => '٢',
            '3' => '٣',
            '4' => '٤',
            '5' => '٥',
            '6' => '٦',
            '7' => '٧',
            '8' => '٨',
            '9' => '٩',
            '.' => '٫',
            ',' => '٬',
        ]);
    }
}

if (! function_exists('localized_number')) {
    function localized_number(int|float|string|null $number, int $decimals = 0): string
    {
        if ($number === null || $number === '') {
            return __('ui.na');
        }

        $decimalSep = app()->getLocale() === 'ar' ? '٫' : '.';
        $thousandsSep = app()->getLocale() === 'ar' ? '٬' : ',';

        return localized_digits(number_format((float) $number, $decimals, $decimalSep, $thousandsSep));
    }
}

if (! function_exists('localized_money')) {
    function localized_money(int|float|string|null $amount): string
    {
        return '$'.localized_number($amount, 2);
    }
}

if (! function_exists('localized_date')) {
    function localized_date(mixed $date, string $format = 'd M Y'): string
    {
        if ($date === null) {
            return __('ui.na');
        }

        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        if (app()->getLocale() === 'ar') {
            $carbon->locale('ar');
        }

        return localized_digits($carbon->translatedFormat($format));
    }
}

if (! function_exists('localized_datetime')) {
    function localized_datetime(mixed $date, string $format = 'd M Y - h:i A'): string
    {
        return localized_date($date, $format);
    }
}

if (! function_exists('localized_entity_name')) {
    /**
     * @param  \App\Models\Office|\App\Models\Category|\App\Models\Service|string|null  $entity
     */
    function localized_entity_name(mixed $entity, string $group = 'offices'): string
    {
        if ($entity instanceof \App\Models\Office
            || $entity instanceof \App\Models\Category
            || $entity instanceof \App\Models\Service) {
            return $entity->localized('name') ?? (string) __('ui.na');
        }

        $name = $entity;

        if ($name === null || $name === '') {
            return (string) __('ui.na');
        }

        if (app()->getLocale() !== 'ar') {
            return $name;
        }

        $key = "entities.{$group}.{$name}";
        $translated = __($key);

        return $translated === $key ? $name : $translated;
    }
}

if (! function_exists('appointment_time_slots')) {
    /**
     * @return list<string>
     */
    function appointment_time_slots(): array
    {
        return ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00'];
    }
}

if (! function_exists('localized_time_option')) {
    function localized_time_option(string $time24): string
    {
        $carbon = Carbon::createFromFormat('H:i', $time24);

        if (app()->getLocale() === 'ar') {
            $carbon->locale('ar');

            return localized_digits($carbon->translatedFormat('h:i A'));
        }

        return $carbon->format('h:i A');
    }
}

if (! function_exists('service_request_statuses')) {
    /**
     * @return list<string>
     */
    function service_request_statuses(): array
    {
        return [
            'pending',
            'in_review',
            'missing_documents',
            'approved',
            'rejected',
            'completed',
        ];
    }
}

if (! function_exists('format_working_hours_for_input')) {
    function format_working_hours_for_input(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return (string) $value;
        }

        if (array_key_exists('days', $value) || array_key_exists('hours', $value)) {
            $days = $value['days'] ?? [];
            $dayStr = is_array($days) ? implode(', ', $days) : (string) $days;
            $hours = (string) ($value['hours'] ?? '');
            $note = trim((string) ($value['note'] ?? ''));
            $line = trim($dayStr.($dayStr !== '' && $hours !== '' ? ' ' : '').$hours);

            return $note !== '' ? $line.' — '.$note : $line;
        }

        if (isset($value['display'])) {
            return (string) $value['display'];
        }

        $parts = [];
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $item = implode(', ', array_map(strval(...), $item));
            }
            $parts[] = is_int($key) ? (string) $item : "{$key}: {$item}";
        }

        return implode('; ', $parts);
    }
}

if (! function_exists('parse_working_hours_input')) {
    /**
     * @return array<string, mixed>|null
     */
    function parse_working_hours_input(?string $input): ?array
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        $decoded = json_decode($input, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return ['hours' => $input];
    }
}
