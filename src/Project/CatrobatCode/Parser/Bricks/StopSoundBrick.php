<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class StopSoundBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::STOP_SOUND_BRICK;
    $this->caption = 'Stop Sound Brick';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
