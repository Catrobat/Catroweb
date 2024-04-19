<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ForVariableFromToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FOR_VARIABLE_FROM_TO_BRICK;
    $this->caption = 'For Variable From To Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
