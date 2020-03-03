<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ZigZagStitchBrick.
 */
class ZigZagStitchBrick extends Brick
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::ZIG_ZAG_STITCH_BRICK;
    $this->caption = 'ZigZag Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
