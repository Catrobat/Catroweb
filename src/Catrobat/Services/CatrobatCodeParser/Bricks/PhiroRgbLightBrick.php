<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class PhiroRgbLightBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_RGB_LIGHT_BRICK;
    $this->caption = 'Set Phiro light';
    $this->setImgFile(Constants::PHIRO_LOOK_BRICK_IMG);
  }
}
