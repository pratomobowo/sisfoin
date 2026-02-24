<?php

namespace App\Support\Audit;

class AuditPayloadSanitizer
{
    private const REDACTED = '[REDACTED]';

    private const SECRET_KEYS = [
        'password',
        'token',
        'api_key',
        'authorization',
        'cookie',
        'session_id',
    ];

    public static function sanitize(array $payload): array
    {
        return self::sanitizeWithMaskedFields($payload)['payload'];
    }

    public static function sanitizeWithMaskedFields(array $payload): array
    {
        $maskedFields = [];

        return [
            'payload' => self::sanitizeArray($payload, '', $maskedFields),
            'masked_fields' => array_values(array_unique($maskedFields)),
        ];
    }

    private static function sanitizeArray(array $payload, string $path, array &$maskedFields): array
    {
        $result = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = is_string($key) ? strtolower($key) : '';
            $currentPath = $path === '' ? (string) $key : $path.'.'.$key;

            if (in_array($normalizedKey, self::SECRET_KEYS, true)) {
                $result[$key] = self::REDACTED;
                $maskedFields[] = $currentPath;

                continue;
            }

            if (is_array($value)) {
                $result[$key] = self::sanitizeArray($value, $currentPath, $maskedFields);

                continue;
            }

            if ($normalizedKey === 'email' && is_string($value)) {
                $result[$key] = self::maskEmail($value);
                $maskedFields[] = $currentPath;

                continue;
            }

            if (in_array($normalizedKey, ['phone', 'hp', 'telp', 'no_hp'], true) && is_string($value)) {
                $result[$key] = self::maskPhone($value);
                $maskedFields[] = $currentPath;

                continue;
            }

            if (in_array($normalizedKey, ['nik', 'npwp', 'identity_number'], true) && is_string($value)) {
                $result[$key] = self::maskIdentityNumber($value);
                $maskedFields[] = $currentPath;

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    private static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($local === '' || $domain === '') {
            return self::REDACTED;
        }

        $prefix = substr($local, 0, 1);

        return sprintf('%s***@%s', $prefix, $domain);
    }

    private static function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 4) {
            return self::REDACTED;
        }

        return substr($phone, 0, 2).str_repeat('*', max(0, $len - 4)).substr($phone, -2);
    }

    private static function maskIdentityNumber(string $number): string
    {
        $len = strlen($number);
        if ($len <= 4) {
            return self::REDACTED;
        }

        return str_repeat('*', $len - 4).substr($number, -4);
    }
}
