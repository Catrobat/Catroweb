<?php

declare(strict_types=1);

namespace App\Application\Framework;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class VersionStrategy implements VersionStrategyInterface
{
  public function __construct(
    #[Autowire('%env(string:APP_VERSION)%')]
    protected string $app_version,
  ) {
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  public function getVersion(string $path): string
  {
    $hash = '';
    $app_env = $_ENV['APP_ENV'];
    if ('dev' === $app_env) {
      $hash = '--'.md5(strval(random_int(0, 999999)));
    }

    if (preg_match('/\?/', $path)) {
      return '&v='.$this->app_version.$hash;
    }

    return '?v='.$this->app_version.$hash;
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  public function applyVersion(string $path): string
  {
    return $path.$this->getVersion($path);
  }
}
