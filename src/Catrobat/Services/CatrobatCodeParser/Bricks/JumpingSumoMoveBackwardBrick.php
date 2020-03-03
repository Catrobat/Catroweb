<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoMoveBackwardBrick.
 */
class JumpingSumoMoveBackwardBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_MOVE_BACKWARD_BRICK;
    $this->caption = 'MOVE Sumo BACKWARD with _ % power for _ seconds';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
