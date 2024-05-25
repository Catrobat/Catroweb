<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class UnknownBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::UNKNOWN_BRICK;
    $this->caption = 'Unknown Brick';
    $this->setImgFile(Constants::UNKNOWN_BRICK_IMG);
  }
}
