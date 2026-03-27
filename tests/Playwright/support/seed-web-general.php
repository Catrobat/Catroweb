<?php

declare(strict_types=1);

use App\System\Testing\Playwright\WebGeneralFixtureSeeder;

$dataset = $argv[1] ?? null;
if (!\is_string($dataset) || '' === trim($dataset)) {
  fwrite(STDERR, "Usage: php tests/Playwright/support/seed-web-general.php <dataset>\n");
  exit(1);
}

$kernel = require __DIR__.'/playwright-bootstrap.php';
$kernel->boot();

try {
  $seeder = new WebGeneralFixtureSeeder($kernel);
  $seeder->seed($dataset);
} finally {
  $kernel->shutdown();
}
