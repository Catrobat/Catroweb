<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class TripleStitchBrick.
 */
class TripleStitchBrick extends Brick
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::TRIPLE_STITCH_BRICK;
    $this->caption = 'Triple Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
