<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class WaitUntilBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WAIT_UNTIL_BRICK;
    $this->caption = 'Wait until _ is true';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
