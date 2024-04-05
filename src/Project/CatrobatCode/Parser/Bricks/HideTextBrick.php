<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class HideTextBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::HIDE_TEXT_BRICK;
    $this->caption = 'Hide variable _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
