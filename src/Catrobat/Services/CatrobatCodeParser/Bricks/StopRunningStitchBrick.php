<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class StopRunningStitchBrick.
 */
class StopRunningStitchBrick extends Brick
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::STOP_RUNNING_STITCH_BRICK;
    $this->caption = 'Stop running Stitch';
    $this->setImgFile(Constants::EMBROIDERY_BRICK_IMG);
  }
}
