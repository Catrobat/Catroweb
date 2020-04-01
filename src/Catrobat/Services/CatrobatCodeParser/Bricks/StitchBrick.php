<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class StitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STITCH_BRICK;
    $this->caption = 'Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
