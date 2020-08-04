<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class JumpingSumoJumpHighBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_JUMP_HIGH_BRICK;
    $this->caption = 'Make a high jump';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
