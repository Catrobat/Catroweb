<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PlayDrumForBeatsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PLAY_DRUM_FOR_BEATS_BRICK;
    $this->caption = 'Play drum _ for _ beats';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
