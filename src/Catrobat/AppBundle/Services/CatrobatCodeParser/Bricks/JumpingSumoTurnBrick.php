<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoTurnBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoTurnBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_TURN_BRICK;
    $this->caption = "Turn around";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}