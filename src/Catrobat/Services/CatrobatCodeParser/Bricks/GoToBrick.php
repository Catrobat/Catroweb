<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class GoToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::GO_TO_BRICK;
    $this->caption = 'Go to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
