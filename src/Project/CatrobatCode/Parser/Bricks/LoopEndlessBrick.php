<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LoopEndlessBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::LOOP_ENDLESS_BRICK;
    $this->caption = 'LoopEndlessBrick (deprecated)';
    $this->setImgFile(Constants::DEPRECATED_BRICK_IMG);
  }
}
