<?php

declare(strict_types=1);

namespace App\Project;

/** One batch of the orphaned-asset garbage collection, continuing after the hash $after. */
final readonly class GarbageCollectOrphanedAssets
{
  public function __construct(public string $after = '')
  {
  }
}
