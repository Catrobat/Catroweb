<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ZigZagStitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ZIG_ZAG_STITCH_BRICK;
    $this->caption = 'ZigZag Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
