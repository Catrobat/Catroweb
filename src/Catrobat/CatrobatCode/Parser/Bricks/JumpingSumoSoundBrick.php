<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class JumpingSumoSoundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_SOUND_BRICK;
    $this->caption = 'Play a sound.';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
