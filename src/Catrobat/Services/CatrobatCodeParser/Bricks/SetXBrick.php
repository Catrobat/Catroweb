<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetXBrick.
 */
class SetXBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_X_BRICK;
    $this->caption = 'Set X to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
