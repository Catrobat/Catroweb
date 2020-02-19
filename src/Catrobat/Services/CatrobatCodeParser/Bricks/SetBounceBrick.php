<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetBounceBrick.
 */
class SetBounceBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_BOUNCE_BRICK;
    $this->caption = 'Set bounce factor to _ %';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
