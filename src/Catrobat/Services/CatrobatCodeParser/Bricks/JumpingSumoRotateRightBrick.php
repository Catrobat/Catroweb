<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoRotateRightBrick.
 */
class JumpingSumoRotateRightBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_ROTATE_RIGHT_BRICK;
    $this->caption = 'ROTATE Sumo RIGHT by _ degrees';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
