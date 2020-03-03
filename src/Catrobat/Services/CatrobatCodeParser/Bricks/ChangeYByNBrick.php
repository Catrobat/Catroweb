<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ChangeYByNBrick.
 */
class ChangeYByNBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHANGE_Y_BY_N_BRICK;
    $this->caption = 'Change Y by _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
