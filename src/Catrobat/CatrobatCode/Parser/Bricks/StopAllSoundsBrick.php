<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class StopAllSoundsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STOP_ALL_SOUNDS_BRICK;
    $this->caption = 'Stop all sounds';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
