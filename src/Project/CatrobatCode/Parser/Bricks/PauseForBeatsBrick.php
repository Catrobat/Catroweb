<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PauseForBeatsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PAUSE_FOR_BEATS_BRICK;
    $this->caption = 'Pause _ beats';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
