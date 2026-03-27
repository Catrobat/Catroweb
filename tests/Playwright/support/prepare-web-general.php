<?php

declare(strict_types=1);

use App\System\Testing\Playwright\WebGeneralFixtureSeeder;

$kernel = require __DIR__.'/playwright-bootstrap.php';
$kernel->boot();

try {
  $seeder = new WebGeneralFixtureSeeder($kernel);
  $seeder->prepareEnvironment();
} finally {
  $kernel->shutdown();
}
