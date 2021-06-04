<?php

namespace App\Utils;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class VersionStrategy implements VersionStrategyInterface
{
  protected string $app_version;

  public function __construct(string $app_version)
  {
    $this->app_version = $app_version;
  }

  public function getVersion($path): string
  {
    if (preg_match('/\?/', $path)) {
      return '&v='.$this->app_version;
    }

    return '?v='.$this->app_version;
  }

  public function applyVersion($path): string
  {
    return $path.$this->getVersion($path);
  }
}
