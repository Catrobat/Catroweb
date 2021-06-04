<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetSizeToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_SIZE_TO_BRICK;
    $this->caption = 'Set size to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
