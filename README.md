# Yeti Autohuolto — PHP edition

This branch converts the Django application to a cPanel-friendly PHP 8.2 application while preserving the existing public frontend.

## Included behavior

- Responsive Home, Services, Booking, About and Contact pages
- Existing Google Maps embed, contact details and static assets
- Booking validation and MySQL persistence
- Booking notification to the garage and confirmation to the customer
- Contact email to the garage with the customer as Reply-To
- Owner login with secure password hashing and session protection
- Dashboard statistics, searchable/filterable booking list and booking editing
- CSRF protection, output escaping, prepared statements, login throttling and spam honeypots

## Requirements

- PHP 8.2 or newer
- PDO MySQL, mbstring and OpenSSL PHP extensions
- MySQL/MariaDB database
- Apache/LiteSpeed with `mod_rewrite`

## First installation

1. Create a private `.env` directly in cPanel with the PHP application, MySQL and company SMTP settings. This file is intentionally not provided through public GitHub.
2. Run `php bin/migrate.php`.
3. Run `php bin/create-admin.php owner` and enter a strong dashboard password.
4. Run `php bin/test-email.php`.
5. Point the domain document root at this directory and confirm that `.htaccess` overrides are enabled.

Never commit `.env` or any password.

## URLs

- Public website: `/`
- Booking: `/booking/`
- Contact: `/contact/`
- Owner login: `/dashboard/login/`
- Booking management: `/dashboard/bookings/`
- Health check: `/health/`

## Checks

GitHub Actions checks every PHP file for syntax errors and runs the database, validation, email and rendering checks in `tests/run.php`.
