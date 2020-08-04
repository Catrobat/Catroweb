<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class StampBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STAMP_BRICK;
    $this->caption = 'Stamp';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
