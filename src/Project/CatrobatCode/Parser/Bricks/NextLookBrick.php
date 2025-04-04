<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class NextLookBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::NEXT_LOOK_BRICK;
    $this->caption = 'Next look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
