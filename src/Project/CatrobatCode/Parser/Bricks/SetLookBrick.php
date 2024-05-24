<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetLookBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_LOOK_BRICK;
    $this->caption = 'Switch to look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
