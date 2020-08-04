<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ChangeBrightnessByNBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_BRIGHTNESS_BY_N_BRICK;
    $this->caption = 'Change brightness by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
