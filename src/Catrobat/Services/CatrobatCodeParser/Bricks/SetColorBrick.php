<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetColorBrick.
 */
class SetColorBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_COLOR_BRICK;
    $this->caption = 'Set color to _ %';

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
