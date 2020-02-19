<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetYBrick.
 */
class SetYBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_Y_BRICK;
    $this->caption = 'Set Y to _';

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
