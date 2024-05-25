<?php

declare(strict_types=1);

//  This file is just a copy of index.php with new Kernel(env="test", debug=false) hardcoded
//  There is no better solution right now than changing the APP_ENV value in the .env file
//  or using this copied file. Else tests like behat may use the test config and initialize
//  the test db correct, but the tests running in a browser just access the dev database!
//

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
  umask(0000);

  Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
  Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
  Request::setTrustedHosts([$trustedHosts]);
}

(new Dotenv())->load('../.env.test');
if (file_exists('../.env.test.local')) {
  (new Dotenv())->load('../.env.test.local');
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
