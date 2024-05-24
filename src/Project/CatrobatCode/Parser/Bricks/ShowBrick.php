<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ShowBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SHOW_BRICK;
    $this->caption = 'Show';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
