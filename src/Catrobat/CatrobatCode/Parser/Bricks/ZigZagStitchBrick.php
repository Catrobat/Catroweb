<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ZigZagStitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ZIG_ZAG_STITCH_BRICK;
    $this->caption = 'ZigZag Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
