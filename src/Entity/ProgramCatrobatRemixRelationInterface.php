<?php

namespace App\Entity;

use DateTime;

interface ProgramCatrobatRemixRelationInterface
{
  public function getAncestor(): Program;

  public function getDescendant(): Program;

  public function getCreatedAt(): ?DateTime;

  public function setCreatedAt(DateTime $created_at): void;

  public function getSeenAt(): ?DateTime;

  public function setSeenAt(DateTime $seen_at): void;
}
