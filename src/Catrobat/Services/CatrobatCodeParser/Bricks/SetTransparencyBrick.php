<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetTransparencyBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_TRANSPARENCY_BRICK;
    $this->caption = 'Set transparency to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
