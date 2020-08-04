<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PlaySoundWaitBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PLAY_SOUND_WAIT_BRICK;
    $this->caption = 'Start sound and wait';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
