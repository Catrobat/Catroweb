<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PlaySoundWaitBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::PLAY_SOUND_WAIT_BRICK;
    $this->caption = 'Start sound and wait';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
