<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ParameterizedBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PARAMETERIZED_BRICK;
    $this->caption = 'Parameterized Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
