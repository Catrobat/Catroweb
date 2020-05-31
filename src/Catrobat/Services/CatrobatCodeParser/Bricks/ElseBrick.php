<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ElseBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ELSE_BRICK;
    $this->caption = 'Else';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
