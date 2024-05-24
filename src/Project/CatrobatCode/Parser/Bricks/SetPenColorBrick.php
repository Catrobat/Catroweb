<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetPenColorBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_PEN_COLOR_BRICK;
    $this->caption = 'Set pen color to Red: _ Green: _ Blue: _';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
