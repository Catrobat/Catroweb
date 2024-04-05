<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ForeverBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FOREVER_BRICK;
    $this->caption = 'Forever';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
