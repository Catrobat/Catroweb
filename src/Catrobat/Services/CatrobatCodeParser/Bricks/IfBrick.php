<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class IfBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::IF_BRICK;
    $this->caption = 'If _ is true then';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
