<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class GoToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::GO_TO_BRICK;
    $this->caption = 'Go to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
