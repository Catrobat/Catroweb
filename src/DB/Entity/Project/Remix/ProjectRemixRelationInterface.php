<?php

namespace App\DB\Entity\Project\Remix;

interface ProjectRemixRelationInterface
{
  public function getUniqueKey(): string;

  public function getDepth(): int;
}
