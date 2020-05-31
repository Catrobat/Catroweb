<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ComeToFrontBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::COME_TO_FRONT_BRICK;
    $this->caption = 'Go to front';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
