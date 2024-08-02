<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context): Kernel {
  (new Dotenv())->load('../.env.test');
  if (file_exists('../.env.test.local')) {
    (new Dotenv())->load('../.env.test.local');
  }

  return new Kernel('test', false);
};
