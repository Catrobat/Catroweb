<?php

namespace App\Application\Framework;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class VersionStrategy implements VersionStrategyInterface
{
  protected string $app_version;

  public function __construct(string $app_version)
  {
    $this->app_version = $app_version;
  }

  public function getVersion(string $path): string
  {
    $hash = '';
    $app_env = $_ENV['APP_ENV'];
    if ('dev' === $app_env) {
      $hash = '--'.md5(strval(rand(0, 999999)));
    }

    if (preg_match('/\?/', $path)) {
      return '&v='.$this->app_version.$hash;
    }

    return '?v='.$this->app_version.$hash;
  }

  public function applyVersion(string $path): string
  {
    return $path.$this->getVersion($path);
  }
}
