<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetBackgroundWaitBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_BACKGROUND_WAIT_BRICK;
    $this->caption = 'Set background and wait';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
