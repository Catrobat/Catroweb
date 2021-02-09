<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PauseForBeatsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PAUSE_FOR_BEATS_BRICK;
    $this->caption = 'Pause _ beats';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
