<?php

namespace App\Utils;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class VersionStrategy implements VersionStrategyInterface
{
  private string $catrobat_version;

  public function __construct(string $catrobat_version)
  {
    $this->catrobat_version = $catrobat_version;
  }

  public function getVersion($path)
  {
    if (preg_match('/\?/', $path))
    {
      return '&v='.$this->catrobat_version;
    }

    return '?v='.$this->catrobat_version;
  }

  public function applyVersion($path)
  {
    return $path.$this->getVersion($path);
  }
}
