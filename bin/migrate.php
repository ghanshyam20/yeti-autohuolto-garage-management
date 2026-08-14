<?php

declare(strict_types=1);

use Yeti\Schema;

require dirname(__DIR__) . '/app/bootstrap.php';

Schema::migrate(service('pdo'));
echo "Database migration completed.\n";
