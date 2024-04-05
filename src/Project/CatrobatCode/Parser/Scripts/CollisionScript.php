<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class CollisionScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::COLLISION_SCRIPT;
    $this->caption = 'Collision Script (deprecated)';
    $this->setImgFile(Constants::DEPRECATED_SCRIPT_IMG);
  }
}
