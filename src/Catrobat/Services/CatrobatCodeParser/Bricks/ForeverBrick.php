<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ForeverBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FOREVER_BRICK;
    $this->caption = 'Forever';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
