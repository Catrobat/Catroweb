<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WhenBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_BRICK;
    $this->caption = 'When tapped';
    $this->setImgFile(Constants::EVENT_BRICK_IMG);
  }
}
