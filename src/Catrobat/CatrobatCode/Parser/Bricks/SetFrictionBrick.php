<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetFrictionBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_FRICTION_BRICK;
    $this->caption = 'Set friction to _ %';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
