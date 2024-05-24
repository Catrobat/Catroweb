<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LegoNxtPlayToneBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::LEGO_NXT_PLAY_TONE_BRICK;
    $this->caption = 'Play NXT Tone';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
