<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetBackgroundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_BACKGROUND_BRICK;
    $this->caption = 'Set background';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
