<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class CloneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLONE_BRICK;
    $this->caption = 'Create clone of _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
