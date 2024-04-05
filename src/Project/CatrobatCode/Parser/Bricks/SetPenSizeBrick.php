<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetPenSizeBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_PEN_SIZE_BRICK;
    $this->caption = 'Set pen size to _';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
