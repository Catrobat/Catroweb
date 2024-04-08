<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PenUpBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PEN_UP_BRICK;
    $this->caption = 'Pen up';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
