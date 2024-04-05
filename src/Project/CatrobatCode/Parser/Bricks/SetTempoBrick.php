<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetTempoBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_TEMPO_BRICK;
    $this->caption = 'Set tempo to _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
