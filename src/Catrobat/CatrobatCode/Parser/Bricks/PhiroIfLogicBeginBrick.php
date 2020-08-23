<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PhiroIfLogicBeginBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_IF_LOGIC_BEGIN_BRICK;
    $this->caption = 'If Phiro _ is activated';
    $this->setImgFile(Constants::PHIRO_CONTROL_BRICK_IMG);
  }
}
