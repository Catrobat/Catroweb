<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class CopyLookBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::COPY_LOOK_BRICK;
    $this->caption = 'Copy look and name it';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
