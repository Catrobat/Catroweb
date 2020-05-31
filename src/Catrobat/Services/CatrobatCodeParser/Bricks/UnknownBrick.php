<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class UnknownBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::UNKNOWN_BRICK;
    $this->caption = 'Unknown Brick';
    $this->setImgFile(Constants::UNKNOWN_BRICK_IMG);
  }
}
