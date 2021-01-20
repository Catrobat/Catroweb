<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetPhysicsObjectTypeBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_PHYSICS_OBJECT_TYPE_BRICK;
    $this->caption = 'Set motion type to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
