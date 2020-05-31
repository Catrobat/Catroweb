<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetTextBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_TEXT_BRICK;
    $this->caption = 'Set Text';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
