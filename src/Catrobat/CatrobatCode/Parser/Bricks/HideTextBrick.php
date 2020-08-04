<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class HideTextBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::HIDE_TEXT_BRICK;
    $this->caption = 'Hide variable _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
