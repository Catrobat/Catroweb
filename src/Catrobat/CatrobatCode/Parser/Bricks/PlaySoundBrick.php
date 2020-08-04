<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PlaySoundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PLAY_SOUND_BRICK;
    $this->caption = 'Start sound';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
