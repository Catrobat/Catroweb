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

  #[\Override]
  public function getVersion(string $path): string
  {
    if ($this->hasContentHash($path)) {
      return '';
    }

    $suffix = '';
    if ('dev' === ($_ENV['APP_ENV'] ?? '')) {
      $suffix = '--'.md5(strval(random_int(0, 999_999)));
    }

    $separator = str_contains($path, '?') ? '&' : '?';

    return $separator.'v='.$this->app_version.$suffix;
  }

  #[\Override]
  public function applyVersion(string $path): string
  {
    return $path.$this->getVersion($path);
  }

  /**
   * Webpack Encore outputs filenames with content/chunk hashes (e.g. runtime-537e323c06d3e.js).
   * Appending ?v=APP_VERSION to these is redundant and causes unnecessary cache busts on deploy.
   */
  private function hasContentHash(string $path): bool
  {
    $file = basename(parse_url($path, PHP_URL_PATH) ?: $path);

    return (bool) preg_match('/[-\.][0-9a-f]{8,}\./', $file);
  }
}
