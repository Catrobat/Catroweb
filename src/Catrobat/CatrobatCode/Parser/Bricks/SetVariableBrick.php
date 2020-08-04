<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetVariableBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_VARIABLE_BRICK;
    $this->caption = 'Set variable _ to _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
