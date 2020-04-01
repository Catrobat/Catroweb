<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class LegoNxtPlayToneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_NXT_PLAY_TONE_BRICK;
    $this->caption = 'Play NXT Tone';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
