<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoJumpHighBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoJumpHighBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_JUMP_HIGH_BRICK;

    $this->caption = "Make a high jump";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}