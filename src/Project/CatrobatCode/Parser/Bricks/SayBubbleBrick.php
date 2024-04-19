<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SayBubbleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SAY_BUBBLE_BRICK;
    $this->caption = 'Say _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
