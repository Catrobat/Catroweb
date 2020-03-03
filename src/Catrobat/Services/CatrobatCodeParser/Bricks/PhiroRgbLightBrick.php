<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PhiroRgbLightBrick.
 */
class PhiroRgbLightBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::PHIRO_RGB_LIGHT_BRICK;
    $this->caption = 'Set Phiro light';
    $this->setImgFile(Constants::PHIRO_LOOK_BRICK_IMG);
  }
}
