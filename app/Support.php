<?php

declare(strict_types=1);

use Yeti\HttpException;

/** @return array<string, mixed> */
function app_config(): array
{
    return $GLOBALS['yeti_config'] ?? [];
}

function config(string $key, mixed $default = null): mixed
{
    $value = app_config();
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function service(string $name): mixed
{
    if (!array_key_exists($name, $GLOBALS['yeti_services'] ?? [])) {
        throw new RuntimeException("Unknown application service: {$name}");
    }
    return $GLOBALS['yeti_services'][$name];
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    $base = rtrim((string) config('app.base_path', ''), '/');
    $path = '/' . ltrim($path, '/');
    return ($base === '' ? '' : $base) . $path;
}

function asset(string $path): string
{
    return url('/static/' . ltrim($path, '/'));
}

/** @param array<string, mixed> $data */
function view(string $name, array $data = []): void
{
    $path = dirname(__DIR__) . '/views/' . $name . '.php';
    if (!is_file($path)) {
        throw new RuntimeException("View not found: {$name}");
    }
    extract($data, EXTR_SKIP);
    require $path;
}

/** @param array<string, mixed> $data */
function render_page(string $name, array $data = []): void
{
    $title = (string) ($data['title'] ?? 'Yeti Autohuolto');
    $flashes = pull_flashes();
    ob_start();
    view($name, $data);
    $content = (string) ob_get_clean();
    require dirname(__DIR__) . '/views/layout.php';
}

function redirect_to(string $path, int $status = 303): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flashes'][] = ['type' => $type, 'message' => $message];
}

/** @return list<array{type: string, message: string}> */
function pull_flashes(): array
{
    $messages = $_SESSION['_flashes'] ?? [];
    unset($_SESSION['_flashes']);
    return is_array($messages) ? $messages : [];
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        throw new HttpException(419, 'Your session expired. Please refresh the page and try again.');
    }
}

function is_owner(): bool
{
    return isset($_SESSION['owner_id'], $_SESSION['owner_username']);
}

function require_owner(): void
{
    if (!is_owner()) {
        flash('warning', 'Please sign in to continue.');
        redirect_to('/dashboard/login/');
    }
}

function owner_username(): string
{
    return (string) ($_SESSION['owner_username'] ?? 'Owner');
}

function now(): string
{
    return (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
}

/** @return array<string, string> */
function service_choices(): array
{
    return [
        'routine_maintenance' => 'Routine Maintenance',
        'engine_diagnostics' => 'Engine Diagnostics',
        'brake_service' => 'Brake Service',
        'tire_service' => 'Tire Service',
        'battery_service' => 'Battery Service',
        'air_conditioning' => 'Air Conditioning',
        'suspension_steering' => 'Suspension & Steering',
        'other' => 'Other',
    ];
}

/** @return array<string, string> */
function time_choices(): array
{
    return [
        'morning' => 'Morning (08:00–12:00)',
        'afternoon' => 'Afternoon (12:00–16:00)',
        'evening' => 'Evening (16:00–18:00)',
        'no_preference' => 'No Preference',
    ];
}

/** @return array<string, string> */
function status_choices(): array
{
    return [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
}

function service_label(string $value): string
{
    return service_choices()[$value] ?? $value;
}

function time_label(string $value): string
{
    return time_choices()[$value] ?? $value;
}

function status_label(string $value): string
{
    return status_choices()[$value] ?? $value;
}

/** @param array<string, mixed> $values */
function form_value(array $values, string $field, string $default = ''): string
{
    $value = $values[$field] ?? $default;
    return is_scalar($value) ? (string) $value : $default;
}

/** @param array<string, string> $errors */
function field_error(array $errors, string $field): string
{
    return $errors[$field] ?? '';
}

function active_nav(string $path): string
{
    $current = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $target = '/' . trim(url($path), '/');
    return $current === $target ? 'active' : '';
}

function query_url(array $changes): string
{
    $query = array_merge($_GET, $changes);
    foreach ($query as $key => $value) {
        if ($value === '' || $value === null) {
            unset($query[$key]);
        }
    }
    return '?' . http_build_query($query);
}
