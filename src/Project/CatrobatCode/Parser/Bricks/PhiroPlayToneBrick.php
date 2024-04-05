<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PhiroPlayToneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_PLAY_TONE_BRICK;
    $this->caption = 'Play Phiro music tone';
    $this->setImgFile(Constants::PHIRO_SOUND_BRICK_IMG);
  }
}
