<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PlayDrumForBeatsBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::PLAY_DRUM_FOR_BEATS_BRICK;
    $this->caption = 'Play drum _ for _ beats';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
