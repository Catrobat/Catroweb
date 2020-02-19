<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class RunningStitchBrick.
 */
class RunningStitchBrick extends Brick
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::RUNNING_STITCH_BRICK;
    $this->caption = 'Stitch is running';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
