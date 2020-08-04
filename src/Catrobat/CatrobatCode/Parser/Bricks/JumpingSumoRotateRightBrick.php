<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class JumpingSumoRotateRightBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_ROTATE_RIGHT_BRICK;
    $this->caption = 'ROTATE Sumo RIGHT by _ degrees';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
