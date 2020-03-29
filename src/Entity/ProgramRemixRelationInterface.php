<?php

namespace App\Entity;

interface ProgramRemixRelationInterface
{
  public function getUniqueKey(): string;

  public function getDepth(): int;
}
