<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class TapAtBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TAP_AT_BRICK;
    $this->caption = 'Tap At';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
