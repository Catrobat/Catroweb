<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PlayNoteForBeatsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PLAY_NOTE_FOR_BEATS_BRICK;
    $this->caption = 'Play note _ for _ beats';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
