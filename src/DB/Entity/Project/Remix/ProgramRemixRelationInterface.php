<?php

declare(strict_types=1);

namespace App\DB\Entity\Project\Remix;

interface ProgramRemixRelationInterface
{
  public function getUniqueKey(): string;

  public function getDepth(): int;
}
