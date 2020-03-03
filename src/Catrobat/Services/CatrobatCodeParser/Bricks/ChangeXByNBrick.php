<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ChangeXByNBrick.
 */
class ChangeXByNBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHANGE_X_BY_N_BRICK;
    $this->caption = 'Change X by _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
