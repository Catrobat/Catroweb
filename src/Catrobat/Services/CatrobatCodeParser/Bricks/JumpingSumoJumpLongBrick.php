<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class JumpingSumoJumpLongBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_JUMP_LONG_BRICK;
    $this->caption = 'Make a long jump';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
