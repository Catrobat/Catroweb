<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class StitchBrick.
 */
class StitchBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::STITCH_BRICK;
    $this->caption = 'Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
