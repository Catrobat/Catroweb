<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ChangeColorByNBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::CHANGE_COLOR_BY_N_BRICK;
    $this->caption = 'Change color by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
