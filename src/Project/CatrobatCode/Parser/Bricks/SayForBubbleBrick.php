<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SayForBubbleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SAY_FOR_BUBBLE_BRICK;
    $this->caption = 'Say _ for _ seconds';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
