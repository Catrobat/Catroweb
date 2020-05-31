<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class EndIfBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ENDIF_BRICK;
    $this->caption = 'End If';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
