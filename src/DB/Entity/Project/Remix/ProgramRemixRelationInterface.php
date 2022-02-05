<?php

namespace App\DB\Entity\Project\Remix;

interface ProgramRemixRelationInterface
{
  public function getUniqueKey(): string;

  public function getDepth(): int;
}
