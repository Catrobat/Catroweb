<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ChangeBrightnessByNBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHANGE_BRIGHTNESS_BY_N_BRICK;
    $this->caption = 'Change brightness by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
