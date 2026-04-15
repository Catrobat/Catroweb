<?php

declare(strict_types=1);

namespace App\DB\Entity\Project\Remix;

interface ProjectRemixRelationInterface
{
  public function getUniqueKey(): string;

  public function getDepth(): int;
}
