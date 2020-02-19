<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetFrictionBrick.
 */
class SetFrictionBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_FRICTION_BRICK;
    $this->caption = 'Set friction to _ %';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
