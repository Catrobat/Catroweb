<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ChangeTempoByNBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_TEMPO_BY_N_BRICK;
    $this->caption = 'Change tempo by _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
