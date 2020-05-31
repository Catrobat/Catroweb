<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class WhenBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_BRICK;
    $this->caption = 'When tapped';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
