<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class JumpingSumoRotateLeftBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_ROTATE_LEFT_BRICK;
    $this->caption = 'ROTATE Sumo LEFT by _ degrees';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
