<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ChangeVariableBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_VARIABLE_BRICK;
    $this->caption = 'Change variable _ by _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
