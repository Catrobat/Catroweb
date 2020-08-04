<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PenUpBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PEN_UP_BRICK;
    $this->caption = 'Pen up';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
