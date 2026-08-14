<?php

declare(strict_types=1);

use Yeti\Mailer;

require dirname(__DIR__) . '/app/bootstrap.php';

/** @var Mailer $mailer */
$mailer = service('mailer');
$recipient = (string) config('mail.owner_address');
$mailer->send(
    $recipient,
    'Yeti PHP website email test',
    "The PHP website connected to the company email successfully.\n\nTime: " . now()
);

echo "Test email sent to {$recipient}.\n";
