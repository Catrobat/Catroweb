<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SpeakBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SPEAK_BRICK;
    $this->caption = 'Speak _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
