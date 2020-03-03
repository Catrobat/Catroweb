<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoRotateLeftBrick.
 */
class JumpingSumoRotateLeftBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_ROTATE_LEFT_BRICK;
    $this->caption = 'ROTATE Sumo LEFT by _ degrees';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
