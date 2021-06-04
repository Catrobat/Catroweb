<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class CollisionScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::COLLISION_SCRIPT;
    $this->caption = 'Collision Script (deprecated)';
    $this->setImgFile(Constants::DEPRECATED_SCRIPT_IMG);
  }
}
