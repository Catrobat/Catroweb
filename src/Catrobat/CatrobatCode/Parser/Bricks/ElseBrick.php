<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ElseBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ELSE_BRICK;
    $this->caption = 'Else';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
