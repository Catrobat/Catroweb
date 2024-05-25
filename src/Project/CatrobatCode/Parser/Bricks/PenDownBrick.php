<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PenDownBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::PEN_DOWN_BRICK;
    $this->caption = 'Pen down';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
