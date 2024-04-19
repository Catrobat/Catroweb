<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class AskBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ASK_BRICK;
    $this->caption = 'Ask _ and store written answer in _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
