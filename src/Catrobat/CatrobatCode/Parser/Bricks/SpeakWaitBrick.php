<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SpeakWaitBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SPEAK_WAIT_BRICK;
    $this->caption = 'Speak _ and wait';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
