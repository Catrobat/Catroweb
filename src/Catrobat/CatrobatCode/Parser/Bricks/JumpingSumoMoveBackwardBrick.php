<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class JumpingSumoMoveBackwardBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_MOVE_BACKWARD_BRICK;
    $this->caption = 'MOVE Sumo BACKWARD with _ % power for _ seconds';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
