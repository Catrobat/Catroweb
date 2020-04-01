<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetRotationStyleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_ROTATION_STYLE_BRICK;
    $this->caption = 'Set rotation style';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
