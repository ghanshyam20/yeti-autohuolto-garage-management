<?php

declare(strict_types=1);

namespace Yeti;

use DateTimeImmutable;

final class Validation
{
    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, string>, errors: array<string, string>}
     */
    public static function booking(array $input, bool $includeStatus = false): array
    {
        $data = [
            'full_name' => self::text($input, 'full_name'),
            'phone_number' => self::text($input, 'phone_number'),
            'email' => strtolower(self::text($input, 'email')),
            'vehicle_make' => self::text($input, 'vehicle_make'),
            'vehicle_model' => self::text($input, 'vehicle_model'),
            'registration_number' => strtoupper(self::text($input, 'registration_number')),
            'service_required' => self::text($input, 'service_required'),
            'problem_description' => self::text($input, 'problem_description'),
            'preferred_date' => self::text($input, 'preferred_date'),
            'preferred_time' => self::text($input, 'preferred_time') ?: 'no_preference',
        ];

        if ($includeStatus) {
            $data['status'] = self::text($input, 'status') ?: 'pending';
        }

        $errors = [];
        foreach ([
            'full_name' => 'Full name',
            'phone_number' => 'Phone number',
            'email' => 'Email address',
            'vehicle_make' => 'Vehicle make',
            'vehicle_model' => 'Vehicle model',
            'service_required' => 'Service required',
            'preferred_date' => 'Preferred date',
        ] as $field => $label) {
            if ($data[$field] === '') {
                $errors[$field] = "{$label} is required.";
            }
        }

        foreach ([
            'full_name' => 100,
            'phone_number' => 25,
            'email' => 254,
            'vehicle_make' => 100,
            'vehicle_model' => 100,
            'registration_number' => 20,
            'service_required' => 50,
            'problem_description' => 5000,
            'preferred_time' => 30,
        ] as $field => $maximum) {
            if (mb_strlen($data[$field]) > $maximum) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be {$maximum} characters or fewer.";
            }
        }

        if ($data['email'] !== '' && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($data['service_required'] !== '' && !array_key_exists($data['service_required'], service_choices())) {
            $errors['service_required'] = 'Choose a valid service.';
        }

        if (!array_key_exists($data['preferred_time'], time_choices())) {
            $errors['preferred_time'] = 'Choose a valid preferred time.';
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $data['preferred_date']);
        $dateErrors = DateTimeImmutable::getLastErrors();
        $invalidDate = $date === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || ($date !== false && $date->format('Y-m-d') !== $data['preferred_date']);

        if ($data['preferred_date'] !== '' && $invalidDate) {
            $errors['preferred_date'] = 'Choose a valid date.';
        } elseif (!$includeStatus && $date !== false && $date < new DateTimeImmutable('today')) {
            $errors['preferred_date'] = 'Please choose today or a future date.';
        }

        if ($includeStatus && !array_key_exists($data['status'], status_choices())) {
            $errors['status'] = 'Choose a valid booking status.';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, string>, errors: array<string, string>}
     */
    public static function contact(array $input): array
    {
        $data = [
            'full_name' => self::text($input, 'full_name'),
            'phone_number' => self::text($input, 'phone_number'),
            'email' => strtolower(self::text($input, 'email')),
            'subject' => self::text($input, 'subject'),
            'message' => self::text($input, 'message'),
        ];

        $errors = [];
        foreach (['full_name' => 'Full name', 'email' => 'Email address', 'message' => 'Message'] as $field => $label) {
            if ($data[$field] === '') {
                $errors[$field] = "{$label} is required.";
            }
        }

        foreach (['full_name' => 100, 'phone_number' => 25, 'email' => 254, 'subject' => 150, 'message' => 3000] as $field => $maximum) {
            if (mb_strlen($data[$field]) > $maximum) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be {$maximum} characters or fewer.";
            }
        }

        if ($data['email'] !== '' && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /** @param array<string, mixed> $input */
    private static function text(array $input, string $key): string
    {
        $value = $input[$key] ?? '';
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
