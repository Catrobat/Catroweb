<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WaitBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WAIT_BRICK;
    $this->caption = 'Wait _ second(s)';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
