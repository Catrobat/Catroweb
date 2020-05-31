<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class CollisionScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::COLLISION_SCRIPT;
    $this->caption = 'Collision Script (deprecated)';
    $this->setImgFile(Constants::DEPRECATED_SCRIPT_IMG);
  }
}
