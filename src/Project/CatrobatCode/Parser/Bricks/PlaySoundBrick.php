<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PlaySoundBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::PLAY_SOUND_BRICK;
    $this->caption = 'Start sound';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
