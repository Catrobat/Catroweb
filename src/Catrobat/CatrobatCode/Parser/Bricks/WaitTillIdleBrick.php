<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WaitTillIdleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WAIT_TILL_IDLE_BRICK;
    $this->caption = 'Wait till idle';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
