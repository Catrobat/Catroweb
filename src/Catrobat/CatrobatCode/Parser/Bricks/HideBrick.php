<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class HideBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::HIDE_BRICK;
    $this->caption = 'Hide';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
