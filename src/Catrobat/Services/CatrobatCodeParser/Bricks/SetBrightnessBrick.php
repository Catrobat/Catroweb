<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetBrightnessBrick.
 */
class SetBrightnessBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_BRIGHTNESS_BRICK;
    $this->caption = 'Set brightness to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
