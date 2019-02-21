<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoNoSoundBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class JumpingSumoNoSoundBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_NO_SOUND_BRICK;
    $this->caption = "No sound";

    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}