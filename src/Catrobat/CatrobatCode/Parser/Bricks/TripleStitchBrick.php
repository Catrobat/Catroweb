<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class TripleStitchBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TRIPLE_STITCH_BRICK;
    $this->caption = 'Triple Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
