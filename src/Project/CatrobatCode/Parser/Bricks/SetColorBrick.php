<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetColorBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_COLOR_BRICK;
    $this->caption = 'Set color to _ %';

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
