<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class JumpingSumoNoSoundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_NO_SOUND_BRICK;
    $this->caption = 'No sound';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
