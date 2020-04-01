<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class PhiroPlayToneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_PLAY_TONE_BRICK;
    $this->caption = 'Play Phiro music tone';
    $this->setImgFile(Constants::PHIRO_SOUND_BRICK_IMG);
  }
}
