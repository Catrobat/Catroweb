<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class JumpingSumoRotateRightBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_ROTATE_RIGHT_BRICK;
    $this->caption = 'ROTATE Sumo RIGHT by _ degrees';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
