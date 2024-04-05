<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class StopAllSoundsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STOP_ALL_SOUNDS_BRICK;
    $this->caption = 'Stop all sounds';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
