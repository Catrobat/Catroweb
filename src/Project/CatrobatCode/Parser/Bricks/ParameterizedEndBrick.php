<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ParameterizedEndBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::PARAMETERIZED_END_BRICK;
    $this->caption = 'Parameterized End Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
