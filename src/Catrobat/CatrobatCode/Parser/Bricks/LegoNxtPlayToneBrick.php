<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class LegoNxtPlayToneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_NXT_PLAY_TONE_BRICK;
    $this->caption = 'Play NXT Tone';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
