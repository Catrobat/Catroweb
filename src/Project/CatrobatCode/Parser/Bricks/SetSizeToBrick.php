<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetSizeToBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_SIZE_TO_BRICK;
    $this->caption = 'Set size to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
