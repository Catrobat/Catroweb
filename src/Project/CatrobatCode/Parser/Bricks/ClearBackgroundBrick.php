<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ClearBackgroundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLEAR_BACKGROUND_BRICK;
    $this->caption = 'Clear';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
