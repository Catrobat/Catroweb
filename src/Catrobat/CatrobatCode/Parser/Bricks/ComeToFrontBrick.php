<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ComeToFrontBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::COME_TO_FRONT_BRICK;
    $this->caption = 'Go to front';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
