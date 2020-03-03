<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoMoveForwardBrick.
 */
class JumpingSumoMoveForwardBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_MOVE_FORWARD_BRICK;
    $this->caption = 'MOVE Sumo FORWARD with _% power for _ seconds';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
