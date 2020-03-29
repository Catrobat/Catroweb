<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class PhiroIfLogicBeginBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_IF_LOGIC_BEGIN_BRICK;
    $this->caption = 'If Phiro _ is activated';
    $this->setImgFile(Constants::PHIRO_CONTROL_BRICK_IMG);
  }
}
