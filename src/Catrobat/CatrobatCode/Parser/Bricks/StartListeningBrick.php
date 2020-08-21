<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class StartListeningBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::START_LISTENING_BRICK;
    $this->caption = 'Start Listening Brick';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
