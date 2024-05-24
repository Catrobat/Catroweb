<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PaintNewLookBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::PAINT_NEW_LOOK_BRICK;
    $this->caption = 'Paint new look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
