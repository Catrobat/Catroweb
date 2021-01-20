<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ParameterizedEndBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PARAMETERIZED_END_BRICK;
    $this->caption = 'Parameterized End Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
