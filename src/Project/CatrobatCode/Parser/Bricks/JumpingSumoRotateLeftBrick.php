<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class JumpingSumoRotateLeftBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_ROTATE_LEFT_BRICK;
    $this->caption = 'ROTATE Sumo LEFT by _ degrees';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
