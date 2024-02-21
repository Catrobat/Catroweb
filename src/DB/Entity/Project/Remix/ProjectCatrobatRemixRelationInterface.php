<?php

namespace App\DB\Entity\Project\Remix;

use App\DB\Entity\Project\Project;

interface ProjectCatrobatRemixRelationInterface
{
  public function getAncestor(): Project;

  public function getDescendant(): Project;

  public function getCreatedAt(): ?\DateTime;

  public function setCreatedAt(\DateTime $created_at): void;

  public function getSeenAt(): ?\DateTime;

  public function setSeenAt(\DateTime $seen_at): void;
}
