<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class CloneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLONE_BRICK;
    $this->caption = 'Create clone of _';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
