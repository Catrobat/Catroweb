<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class JumpingSumoSoundBrick.
 */
class JumpingSumoSoundBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::JUMP_SUMO_SOUND_BRICK;
    $this->caption = 'Play a sound.';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
