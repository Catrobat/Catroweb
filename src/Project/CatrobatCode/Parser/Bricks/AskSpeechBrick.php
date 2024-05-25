<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class AskSpeechBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::ASK_SPEECH_BRICK;
    $this->caption = 'Ask _ and store written answer in _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
