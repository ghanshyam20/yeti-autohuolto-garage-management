<?php

declare(strict_types=1);

use Yeti\AdminRepository;
use Yeti\BookingRepository;
use Yeti\Database;
use Yeti\Env;
use Yeti\LogMailer;
use Yeti\SmtpMailer;

$root = dirname(__DIR__);

require __DIR__ . '/Env.php';
require __DIR__ . '/HttpException.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Schema.php';
require __DIR__ . '/AdminRepository.php';
require __DIR__ . '/BookingRepository.php';
require __DIR__ . '/Validation.php';
require __DIR__ . '/Mailer.php';
require __DIR__ . '/Support.php';

Env::load($root . '/.env');

$GLOBALS['yeti_config'] = [
    'app' => [
        'environment' => (string) Env::get('APP_ENV', 'production'),
        'debug' => Env::bool('APP_DEBUG', false),
        'url' => (string) Env::get('APP_URL', 'https://yetiautohuolto.fi'),
        'base_path' => (string) Env::get('APP_BASE_PATH', ''),
        'timezone' => (string) Env::get('APP_TIMEZONE', 'Europe/Helsinki'),
    ],
    'database' => [
        'driver' => (string) Env::get('DB_DRIVER', 'mysql'),
        'host' => (string) Env::get('DB_HOST', 'localhost'),
        'port' => (int) Env::get('DB_PORT', 3306),
        'database' => (string) Env::get('DB_DATABASE', ''),
        'username' => (string) Env::get('DB_USERNAME', ''),
        'password' => (string) Env::get('DB_PASSWORD', ''),
    ],
    'mail' => [
        'transport' => (string) Env::get('MAIL_TRANSPORT', 'smtp'),
        'host' => (string) Env::get('MAIL_HOST', 'mail.yetiautohuolto.fi'),
        'port' => (int) Env::get('MAIL_PORT', 587),
        'encryption' => (string) Env::get('MAIL_ENCRYPTION', 'tls'),
        'username' => (string) Env::get('MAIL_USERNAME', 'info@yetiautohuolto.fi'),
        'password' => (string) Env::get('MAIL_PASSWORD', ''),
        'from_address' => (string) Env::get('MAIL_FROM_ADDRESS', 'info@yetiautohuolto.fi'),
        'from_name' => (string) Env::get('MAIL_FROM_NAME', 'Yeti Autohuolto'),
        'owner_address' => (string) Env::get('GARAGE_OWNER_EMAIL', 'info@yetiautohuolto.fi'),
    ],
];

date_default_timezone_set((string) config('app.timezone', 'Europe/Helsinki'));

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_name('yeti_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => url('/'),
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

$database = new Database((array) config('database'));
$pdo = $database->connection();
$mailConfig = (array) config('mail');
$mailer = $mailConfig['transport'] === 'log'
    ? new LogMailer($root . '/storage/logs/mail.log')
    : new SmtpMailer($mailConfig);

$GLOBALS['yeti_services'] = [
    'database' => $database,
    'pdo' => $pdo,
    'bookings' => new BookingRepository($pdo),
    'admins' => new AdminRepository($pdo),
    'mailer' => $mailer,
];

require __DIR__ . '/controllers.php';
