<?php

declare(strict_types=1);

use Yeti\AdminRepository;
use Yeti\Schema;

require dirname(__DIR__) . '/app/bootstrap.php';

Schema::migrate(service('pdo'));

$username = trim((string) ($argv[1] ?? ''));
if ($username === '') {
    $username = trim((string) readline('Owner username [owner]: '));
}
$username = $username !== '' ? $username : 'owner';

$password = (string) getenv('OWNER_INITIAL_PASSWORD');
if ($password === '') {
    fwrite(STDOUT, 'Owner password: ');
    if (function_exists('shell_exec')) {
        shell_exec('stty -echo');
    }
    $password = trim((string) fgets(STDIN));
    if (function_exists('shell_exec')) {
        shell_exec('stty echo');
    }
    fwrite(STDOUT, "\n");
}

if (mb_strlen($password) < 12) {
    fwrite(STDERR, "Password must contain at least 12 characters.\n");
    exit(1);
}

/** @var AdminRepository $admins */
$admins = service('admins');
$admins->save($username, password_hash($password, PASSWORD_DEFAULT));

echo "Owner account ready: {$username}\n";
