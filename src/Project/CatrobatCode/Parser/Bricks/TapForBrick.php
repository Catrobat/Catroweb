<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class TapForBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::TAP_FOR_BRICK;
    $this->caption = 'Tap For Brick';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
