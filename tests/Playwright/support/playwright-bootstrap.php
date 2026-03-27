<?php

declare(strict_types=1);

use App\Kernel;

require dirname(__DIR__, 3).'/config/bootstrap.php';

putenv('APP_ENV=test');
$_ENV['APP_ENV'] = 'test';
$_SERVER['APP_ENV'] = 'test';

return new Kernel('test', false);
