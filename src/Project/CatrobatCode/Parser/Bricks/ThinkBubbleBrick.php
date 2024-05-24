<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ThinkBubbleBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::THINK_BUBBLE_BRICK;
    $this->caption = 'Think _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
