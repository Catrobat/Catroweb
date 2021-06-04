<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class GoToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::GO_TO_BRICK;
    $this->caption = 'Go to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
