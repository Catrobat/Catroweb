<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class AskSpeechBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ASK_SPEECH_BRICK;
    $this->caption = 'Ask _ and store written answer in _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
