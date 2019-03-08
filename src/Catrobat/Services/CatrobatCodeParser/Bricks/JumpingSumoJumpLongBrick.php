<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoJumpLongBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoJumpLongBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_JUMP_LONG_BRICK;

    $this->caption = "Make a long jump";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}