<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ThinkForBubbleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::THINK_FOR_BUBBLE_BRICK;
    $this->caption = 'Think _ for _ seconds';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
