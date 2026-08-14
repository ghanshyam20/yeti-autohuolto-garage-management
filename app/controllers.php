<?php

declare(strict_types=1);

use Yeti\AdminRepository;
use Yeti\BookingRepository;
use Yeti\HttpException;
use Yeti\Mailer;
use Yeti\Validation;

function dispatch(string $method, string $path): void
{
    $publicGetRoutes = [
        '/' => ['pages/home', 'Yeti Autohuolto | Professional Auto Service'],
        '/services/' => ['pages/services', 'Services | Yeti Autohuolto'],
        '/booking/' => ['pages/booking', 'Booking | Yeti Autohuolto'],
        '/about/' => ['pages/about', 'About | Yeti Autohuolto'],
        '/contact/' => ['pages/contact', 'Contact | Yeti Autohuolto'],
    ];

    if ($method === 'GET' && isset($publicGetRoutes[$path])) {
        [$viewName, $title] = $publicGetRoutes[$path];
        render_page($viewName, [
            'title' => $title,
            'values' => [],
            'errors' => [],
        ]);
        return;
    }

    if ($method === 'POST' && $path === '/booking/') {
        submit_booking();
        return;
    }

    if ($method === 'POST' && $path === '/contact/') {
        submit_contact();
        return;
    }

    if ($path === '/dashboard/login/' && in_array($method, ['GET', 'POST'], true)) {
        owner_login($method);
        return;
    }

    if ($method === 'POST' && $path === '/dashboard/logout/') {
        owner_logout();
        return;
    }

    if ($method === 'GET' && $path === '/dashboard/') {
        show_dashboard();
        return;
    }

    if ($method === 'GET' && $path === '/dashboard/bookings/') {
        show_bookings();
        return;
    }

    if (preg_match('#^/dashboard/bookings/(\d+)/$#', $path, $matches) === 1 && in_array($method, ['GET', 'POST'], true)) {
        edit_booking((int) $matches[1], $method);
        return;
    }

    if ($method === 'POST' && preg_match('#^/dashboard/bookings/(\d+)/delete/$#', $path, $matches) === 1) {
        delete_booking((int) $matches[1]);
        return;
    }

    if ($method === 'GET' && $path === '/admin/') {
        redirect_to('/dashboard/');
    }

    if ($method === 'GET' && $path === '/health/') {
        health_check();
        return;
    }

    throw new HttpException(404, 'The page you requested could not be found.');
}

function submit_booking(): void
{
    verify_csrf();

    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        flash('success', "Thank you! Your booking request has been received. We'll contact you shortly.");
        redirect_to('/booking/');
    }

    $result = Validation::booking($_POST);
    if ($result['errors'] !== []) {
        http_response_code(422);
        render_page('pages/booking', [
            'title' => 'Booking | Yeti Autohuolto',
            'values' => $result['data'],
            'errors' => $result['errors'],
        ]);
        return;
    }

    /** @var BookingRepository $bookings */
    $bookings = service('bookings');
    $bookingId = $bookings->create($result['data']);
    $booking = $bookings->find($bookingId);

    if ($booking === null) {
        throw new RuntimeException('The booking was saved but could not be loaded.');
    }

    $failed = false;
    foreach ([
        fn () => send_owner_booking_email($booking),
        fn () => send_customer_booking_email($booking),
    ] as $send) {
        try {
            $send();
        } catch (Throwable $exception) {
            $failed = true;
            error_log('Booking ' . $bookingId . ' was saved, but email failed: ' . $exception->getMessage());
        }
    }

    if ($failed) {
        flash('warning', 'Your booking request was saved. If you do not receive an email, please call +358 45 156 6199.');
    } else {
        flash('success', "Thank you! Your booking request has been received. We'll contact you shortly.");
    }

    redirect_to('/booking/');
}

function submit_contact(): void
{
    verify_csrf();

    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        flash('success', 'Thank you! Your message has been sent.');
        redirect_to('/contact/');
    }

    $result = Validation::contact($_POST);
    if ($result['errors'] !== []) {
        http_response_code(422);
        render_page('pages/contact', [
            'title' => 'Contact | Yeti Autohuolto',
            'values' => $result['data'],
            'errors' => $result['errors'],
        ]);
        return;
    }

    $data = $result['data'];
    $subject = trim(str_replace(["\r", "\n"], ' ', $data['subject'])) ?: 'Website contact request';
    $body = "A new contact request was submitted on yetiautohuolto.fi.\n\n"
        . "Name: {$data['full_name']}\n"
        . 'Phone: ' . ($data['phone_number'] !== '' ? $data['phone_number'] : '-') . "\n"
        . "Email: {$data['email']}\n\n"
        . "Message:\n{$data['message']}";

    try {
        /** @var Mailer $mailer */
        $mailer = service('mailer');
        $mailer->send(
            (string) config('mail.owner_address'),
            'Website contact: ' . $subject,
            $body,
            $data['email']
        );
    } catch (Throwable $exception) {
        error_log('Contact email failed: ' . $exception->getMessage());
        flash('danger', 'We could not send your message. Please call +358 45 156 6199.');
        render_page('pages/contact', [
            'title' => 'Contact | Yeti Autohuolto',
            'values' => $data,
            'errors' => [],
        ]);
        return;
    }

    flash('success', 'Thank you! Your message has been sent.');
    redirect_to('/contact/');
}

function owner_login(string $method): void
{
    if (is_owner()) {
        redirect_to('/dashboard/');
    }

    $error = '';
    $username = '';

    if ($method === 'POST') {
        verify_csrf();
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $attempts = (int) ($_SESSION['login_attempts'] ?? 0);
        $lastAttempt = (int) ($_SESSION['last_login_attempt'] ?? 0);

        if ($attempts >= 5 && (time() - $lastAttempt) < 300) {
            $error = 'Too many attempts. Please wait five minutes and try again.';
        } else {
            /** @var AdminRepository $admins */
            $admins = service('admins');
            $admin = $admins->findByUsername($username);

            if ($admin !== null && password_verify($password, (string) $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['owner_id'] = (int) $admin['id'];
                $_SESSION['owner_username'] = (string) $admin['username'];
                unset($_SESSION['login_attempts'], $_SESSION['last_login_attempt']);
                redirect_to('/dashboard/');
            }

            $_SESSION['login_attempts'] = $attempts + 1;
            $_SESSION['last_login_attempt'] = time();
            $error = 'Invalid username or password.';
        }
    }

    view('dashboard/login', [
        'title' => 'Owner Login | Yeti Autohuolto',
        'error' => $error,
        'username' => $username,
    ]);
}

function owner_logout(): void
{
    verify_csrf();
    unset($_SESSION['owner_id'], $_SESSION['owner_username']);
    session_regenerate_id(true);
    flash('success', 'You have been signed out.');
    redirect_to('/dashboard/login/');
}

function show_dashboard(): void
{
    require_owner();
    /** @var BookingRepository $bookings */
    $bookings = service('bookings');

    view('dashboard/index', [
        'totalBookings' => $bookings->count(),
        'pendingBookings' => $bookings->count('pending'),
        'confirmedBookings' => $bookings->count('confirmed'),
        'completedBookings' => $bookings->count('completed'),
        'recentBookings' => $bookings->recent(5),
    ]);
}

function show_bookings(): void
{
    require_owner();
    /** @var BookingRepository $bookings */
    $bookings = service('bookings');

    $filters = [
        'search' => trim((string) ($_GET['search'] ?? '')),
        'status' => trim((string) ($_GET['status'] ?? '')),
        'service' => trim((string) ($_GET['service'] ?? '')),
        'date' => trim((string) ($_GET['date'] ?? '')),
    ];

    view('dashboard/bookings', [
        'result' => $bookings->paginate($filters, (int) ($_GET['page'] ?? 1), 20),
        'filters' => $filters,
        'flashes' => pull_flashes(),
    ]);
}

function edit_booking(int $id, string $method): void
{
    require_owner();
    /** @var BookingRepository $bookings */
    $bookings = service('bookings');
    $booking = $bookings->find($id);

    if ($booking === null) {
        throw new HttpException(404, 'Booking not found.');
    }

    $errors = [];
    $values = $booking;

    if ($method === 'POST') {
        verify_csrf();
        $result = Validation::booking($_POST, true);
        $errors = $result['errors'];
        $values = array_merge($booking, $result['data']);

        if ($errors === []) {
            $bookings->update($id, $result['data']);
            flash('success', 'Booking updated successfully.');
            redirect_to('/dashboard/bookings/' . $id . '/');
        }
    }

    view('dashboard/booking_edit', [
        'booking' => $booking,
        'values' => $values,
        'errors' => $errors,
        'flashes' => pull_flashes(),
    ]);
}

function delete_booking(int $id): void
{
    require_owner();
    verify_csrf();
    /** @var BookingRepository $bookings */
    $bookings = service('bookings');

    if ($bookings->find($id) === null) {
        throw new HttpException(404, 'Booking not found.');
    }

    $bookings->delete($id);
    flash('success', 'Booking deleted.');
    redirect_to('/dashboard/bookings/');
}

function health_check(): void
{
    header('Content-Type: application/json; charset=UTF-8');
    service('pdo')->query('SELECT 1');
    echo json_encode(['status' => 'ok', 'time' => now()], JSON_THROW_ON_ERROR);
}

/** @param array<string, mixed> $booking */
function send_customer_booking_email(array $booking): void
{
    /** @var Mailer $mailer */
    $mailer = service('mailer');
    $body = "Hello {$booking['full_name']},\n\n"
        . "Thank you for choosing Yeti Autohuolto.\n\n"
        . "We have received your booking request successfully.\n\n"
        . "Booking Details\n-----------------------\n"
        . "Vehicle: {$booking['vehicle_make']} {$booking['vehicle_model']}\n"
        . 'Requested Service: ' . service_label((string) $booking['service_required']) . "\n"
        . "Preferred Date: {$booking['preferred_date']}\n"
        . 'Preferred Time: ' . time_label((string) $booking['preferred_time']) . "\n"
        . "Current Status: Pending\n\n"
        . "Our mechanic will review your request and contact you soon to confirm your appointment.\n\n"
        . "Thank you,\nYeti Autohuolto\nEspoo, Finland";

    $mailer->send((string) $booking['email'], 'Booking Request Received | Yeti Autohuolto', $body);
}

/** @param array<string, mixed> $booking */
function send_owner_booking_email(array $booking): void
{
    /** @var Mailer $mailer */
    $mailer = service('mailer');
    $body = "A new booking request has been received.\n\n"
        . "CUSTOMER\n----------------------------------------\n"
        . "Name: {$booking['full_name']}\nPhone: {$booking['phone_number']}\nEmail: {$booking['email']}\n\n"
        . "VEHICLE\n----------------------------------------\n"
        . "Make: {$booking['vehicle_make']}\nModel: {$booking['vehicle_model']}\n"
        . 'Registration: ' . ($booking['registration_number'] ?: '-') . "\n\n"
        . "SERVICE\n----------------------------------------\n"
        . 'Requested Service: ' . service_label((string) $booking['service_required']) . "\n"
        . 'Problem Description: ' . ($booking['problem_description'] ?: '-') . "\n"
        . "Preferred Date: {$booking['preferred_date']}\n"
        . 'Preferred Time: ' . time_label((string) $booking['preferred_time']) . "\n\n"
        . "STATUS\nPending\n\nPlease log into the dashboard to review this booking.\n\nYeti Autohuolto";

    $mailer->send(
        (string) config('mail.owner_address'),
        'New Booking Request - ' . (string) $booking['full_name'],
        $body,
        (string) $booking['email']
    );
}
