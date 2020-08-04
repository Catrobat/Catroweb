<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class JumpingSumoNoSoundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_NO_SOUND_BRICK;
    $this->caption = 'No sound';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
