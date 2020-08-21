<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class TapForBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TAP_FOR_BRICK;
    $this->caption = 'Tap For Brick';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
