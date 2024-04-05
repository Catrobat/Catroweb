<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SpeakBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SPEAK_BRICK;
    $this->caption = 'Speak _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
