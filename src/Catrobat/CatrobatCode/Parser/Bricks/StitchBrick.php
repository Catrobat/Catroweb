<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class StitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STITCH_BRICK;
    $this->caption = 'Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
