<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class TapAtBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TAP_AT_BRICK;
    $this->caption = 'Tap At';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
