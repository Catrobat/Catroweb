<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class StopSoundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STOP_SOUND_BRICK;
    $this->caption = 'Stop Sound Brick';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
