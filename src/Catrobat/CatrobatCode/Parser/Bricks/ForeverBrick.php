<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ForeverBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FOREVER_BRICK;
    $this->caption = 'Forever';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
