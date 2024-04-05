<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class JumpingSumoAnimationBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_ANIMATIONS_BRICK;
    $this->caption = 'Animation Jumping Sumo';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
