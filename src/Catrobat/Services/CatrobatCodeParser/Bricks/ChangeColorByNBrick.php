<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ChangeColorByNBrick.
 */
class ChangeColorByNBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHANGE_COLOR_BY_N_BRICK;
    $this->caption = 'Change color by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
