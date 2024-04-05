<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class JumpingSumoMoveForwardBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_MOVE_FORWARD_BRICK;
    $this->caption = 'MOVE Sumo FORWARD with _% power for _ seconds';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
