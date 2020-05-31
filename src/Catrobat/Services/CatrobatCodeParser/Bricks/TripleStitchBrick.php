<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class TripleStitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TRIPLE_STITCH_BRICK;
    $this->caption = 'Triple Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
