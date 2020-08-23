<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ForVariableFromToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FOR_VARIABLE_FROM_TO_BRICK;
    $this->caption = 'For Variable From To Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
