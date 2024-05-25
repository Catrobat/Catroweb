<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetLookByIndexBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_LOOK_BY_INDEX_BRICK;
    $this->caption = 'Switch to look number';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
