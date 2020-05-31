<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetSizeToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_SIZE_TO_BRICK;
    $this->caption = 'Set size to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
