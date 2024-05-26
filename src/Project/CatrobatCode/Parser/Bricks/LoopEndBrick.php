<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LoopEndBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::LOOP_END_BRICK;
    $this->caption = 'End of loop';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
