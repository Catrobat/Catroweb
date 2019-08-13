<?php

//  This file is just a copy of index.php with new Kernel(env="test", debug=false) hardcoded
//  There is no better solution right now than changing the APP_ENV value in the .env file
//  or using this copied file. Else tests like behat may use the test config and initialize
//  the test db correct, but the tests running in a browser just access the dev database!
//


use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/config/bootstrap.php';

if ($_SERVER['APP_DEBUG'])
{
  umask(0000);

  Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false)
{
  Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false)
{
  Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel("test", false);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);