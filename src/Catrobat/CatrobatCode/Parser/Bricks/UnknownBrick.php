<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class UnknownBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::UNKNOWN_BRICK;
    $this->caption = 'Unknown Brick';
    $this->setImgFile(Constants::UNKNOWN_BRICK_IMG);
  }
}
