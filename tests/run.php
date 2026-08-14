<?php

declare(strict_types=1);

use Yeti\AdminRepository;
use Yeti\BookingRepository;
use Yeti\Mailer;
use Yeti\Schema;
use Yeti\Validation;

$root = dirname(__DIR__);
$databasePath = sys_get_temp_dir() . '/yeti-php-test-' . bin2hex(random_bytes(6)) . '.sqlite';
$mailPath = $root . '/storage/logs/mail.log';
@unlink($databasePath);
@unlink($mailPath);

putenv('APP_ENV=testing');
putenv('APP_DEBUG=true');
putenv('APP_TIMEZONE=Europe/Helsinki');
putenv('DB_DRIVER=sqlite');
putenv('DB_DATABASE=' . $databasePath);
putenv('MAIL_TRANSPORT=log');

require $root . '/app/bootstrap.php';

$tests = 0;

function assert_true(bool $condition, string $message): void
{
    global $tests;
    $tests++;
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

Schema::migrate(service('pdo'));

$future = (new DateTimeImmutable('+2 days'))->format('Y-m-d');
$validBooking = [
    'full_name' => 'Test Customer',
    'phone_number' => '+358451234567',
    'email' => 'customer@example.com',
    'vehicle_make' => 'Toyota',
    'vehicle_model' => 'Corolla',
    'registration_number' => 'abc-123',
    'service_required' => 'routine_maintenance',
    'problem_description' => 'Annual service',
    'preferred_date' => $future,
    'preferred_time' => 'morning',
];

$validation = Validation::booking($validBooking);
assert_true($validation['errors'] === [], 'Valid booking passes validation.');
assert_true($validation['data']['registration_number'] === 'ABC-123', 'Registration is normalized.');

$pastBooking = $validBooking;
$pastBooking['preferred_date'] = (new DateTimeImmutable('-1 day'))->format('Y-m-d');
assert_true(isset(Validation::booking($pastBooking)['errors']['preferred_date']), 'Past dates are rejected.');

/** @var BookingRepository $bookings */
$bookings = service('bookings');
$id = $bookings->create($validation['data']);
assert_true($id > 0, 'Booking is inserted.');
assert_true($bookings->count() === 1, 'Total count is correct.');
assert_true($bookings->count('pending') === 1, 'Pending count is correct.');

$booking = $bookings->find($id);
assert_true($booking !== null && $booking['email'] === 'customer@example.com', 'Booking can be loaded.');

$updateData = array_merge($validation['data'], ['status' => 'confirmed']);
$bookings->update($id, $updateData);
assert_true($bookings->count('confirmed') === 1, 'Booking status can be updated.');
assert_true($bookings->paginate(['search' => 'ABC-123'])['total'] === 1, 'Booking search works.');

/** @var AdminRepository $admins */
$admins = service('admins');
$hash = password_hash('test-password-123', PASSWORD_DEFAULT);
$admins->save('owner', $hash);
$admin = $admins->findByUsername('owner');
assert_true($admin !== null && password_verify('test-password-123', $admin['password_hash']), 'Owner authentication data works.');

$contact = Validation::contact([
    'full_name' => 'Test Customer',
    'phone_number' => '+358451234567',
    'email' => 'customer@example.com',
    'subject' => 'Service question',
    'message' => 'Can you service my car next week?',
]);
assert_true($contact['errors'] === [], 'Contact form passes validation.');

/** @var Mailer $mailer */
$mailer = service('mailer');
$mailer->send('info@yetiautohuolto.fi', 'Test message', 'Email body', 'customer@example.com');
assert_true(is_file($mailPath) && str_contains((string) file_get_contents($mailPath), 'Test message'), 'Email transport is invoked.');

ob_start();
render_page('pages/contact', ['title' => 'Contact', 'values' => [], 'errors' => []]);
$html = (string) ob_get_clean();
assert_true(str_contains($html, 'Pikkukouluntie 4'), 'Contact address is rendered.');
assert_true(str_contains($html, 'google.com/maps/embed'), 'Google Maps embed is rendered.');
assert_true(str_contains($html, 'width=device-width'), 'Responsive viewport is rendered.');

$bookings->delete($id);
assert_true($bookings->count() === 0, 'Booking can be deleted.');

@unlink($databasePath);
@unlink($mailPath);

echo "All {$tests} PHP application checks passed.\n";
