<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class CloneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLONE_BRICK;
    $this->caption = 'Create clone of _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
