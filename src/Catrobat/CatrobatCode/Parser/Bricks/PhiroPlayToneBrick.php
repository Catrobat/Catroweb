<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PhiroPlayToneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_PLAY_TONE_BRICK;
    $this->caption = 'Play Phiro music tone';
    $this->setImgFile(Constants::PHIRO_SOUND_BRICK_IMG);
  }
}
