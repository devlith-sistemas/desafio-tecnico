<?php

namespace App\Support;

class BrazilianDocumentFormatter
{
    public static function cpf(int|string|null $value): string
    {
        $digits = self::digits($value);

        if (strlen($digits) !== 11) {
            return self::original($value);
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 3),
            substr($digits, 9, 2),
        );
    }

    public static function rg(int|string|null $value): string
    {
        $digits = self::digits($value);

        if ($digits === '') {
            return '';
        }

        $digits = str_pad($digits, 9, '0', STR_PAD_LEFT);

        if (strlen($digits) !== 9) {
            return self::original($value);
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 3),
            substr($digits, 5, 3),
            substr($digits, 8, 1),
        );
    }

    public static function cep(int|string|null $value): string
    {
        $digits = self::digits($value);

        if (strlen($digits) !== 8) {
            return self::original($value);
        }

        return sprintf('%s-%s', substr($digits, 0, 5), substr($digits, 5, 3));
    }

    private static function digits(int|string|null $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    private static function original(int|string|null $value): string
    {
        return trim((string) $value);
    }
}
