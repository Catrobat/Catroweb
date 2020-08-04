<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetBounceBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_BOUNCE_BRICK;
    $this->caption = 'Set bounce factor to _ %';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
