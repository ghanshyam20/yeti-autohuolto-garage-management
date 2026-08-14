<?php

declare(strict_types=1);

use Yeti\HttpException;

require __DIR__ . '/app/bootstrap.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . $path)) {
    return false;
}

$basePath = rtrim((string) config('app.base_path', ''), '/');

if ($basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

$path = '/' . trim($path, '/');
if ($path !== '/') {
    $path .= '/';
}

try {
    dispatch($method, $path);
} catch (HttpException $exception) {
    http_response_code($exception->statusCode);
    render_page('errors/http', [
        'title' => $exception->statusCode === 404 ? 'Page Not Found' : 'Request Error',
        'statusCode' => $exception->statusCode,
        'message' => $exception->getMessage(),
    ]);
} catch (Throwable $exception) {
    error_log($exception->__toString());
    http_response_code(500);

    if ((bool) config('app.debug', false)) {
        echo '<pre>' . e($exception->__toString()) . '</pre>';
        exit;
    }

    render_page('errors/http', [
        'title' => 'Temporarily Unavailable',
        'statusCode' => 500,
        'message' => 'Something went wrong. Please try again or call +358 45 156 6199.',
    ]);
}
