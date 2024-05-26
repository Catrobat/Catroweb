<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class StartListeningBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::START_LISTENING_BRICK;
    $this->caption = 'Start Listening Brick';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
