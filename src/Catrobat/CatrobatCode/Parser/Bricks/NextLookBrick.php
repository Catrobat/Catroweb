<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class NextLookBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::NEXT_LOOK_BRICK;
    $this->caption = 'Next look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
