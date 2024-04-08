<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ShowTextBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SHOW_TEXT_BRICK;
    $this->caption = 'Show variable _ at X: _ Y: _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
