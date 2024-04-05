<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class JumpingSumoJumpLongBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_JUMP_LONG_BRICK;
    $this->caption = 'Make a long jump';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
