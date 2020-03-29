<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetPhysicsObjectTypeBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_PHYSICS_OBJECT_TYPE_BRICK;
    $this->caption = 'Set motion type to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
