<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetVariableBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_VARIABLE_BRICK;
    $this->caption = 'Set variable _ to _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
